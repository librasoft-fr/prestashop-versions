<?php

namespace BrianHenryIE\Strauss\Console\Commands;

use BrianHenryIE\Strauss\Composer\ComposerPackage;
use BrianHenryIE\Strauss\Composer\Extra\StraussConfig;
use BrianHenryIE\Strauss\Composer\ProjectComposerPackage;
use BrianHenryIE\Strauss\Files\DiscoveredFiles;
use BrianHenryIE\Strauss\Pipeline\Autoload;
use BrianHenryIE\Strauss\Pipeline\ChangeEnumerator;
use BrianHenryIE\Strauss\Pipeline\Cleanup;
use BrianHenryIE\Strauss\Pipeline\Copier;
use BrianHenryIE\Strauss\Pipeline\DependenciesEnumerator;
use BrianHenryIE\Strauss\Pipeline\FileCopyScanner;
use BrianHenryIE\Strauss\Pipeline\FileEnumerator;
use BrianHenryIE\Strauss\Pipeline\FileSymbolScanner;
use BrianHenryIE\Strauss\Pipeline\Licenser;
use BrianHenryIE\Strauss\Pipeline\Prefixer;
use BrianHenryIE\Strauss\Types\DiscoveredSymbols;
use Exception;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class DependenciesCommand extends Command
{
    use LoggerAwareTrait;

    
    protected string $workingDir;

    protected StraussConfig $config;

    protected ProjectComposerPackage $projectComposerPackage;

    
    protected Prefixer $replacer;

    protected DependenciesEnumerator $dependenciesEnumerator;

    
    protected array $flatDependencyTree = [];

    
    protected DiscoveredFiles $discoveredFiles;
    protected DiscoveredSymbols $discoveredSymbols;

    
    protected function configure()
    {
        $this->setName('dependencies');
        $this->setDescription("Copy composer's `require` and prefix their namespace and classnames.");
        $this->setHelp('');

        $this->addOption(
            'updateCallSites',
            null,
            InputArgument::OPTIONAL,
            'Should replacements also be performed in project files? true|list,of,paths|false'
        );

        $this->addOption(
            'deleteVendorPackages',
            null,
            InputArgument::OPTIONAL,
            'Should original packages be deleted after copying? true|false'
        );
    }

    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setLogger(
            new ConsoleLogger(
                $output,
                [ LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL ]
            )
        );

        $workingDir       = getcwd() . DIRECTORY_SEPARATOR;
        $this->workingDir = $workingDir;

        try {
            $this->loadProjectComposerPackage();
            $this->loadConfigFromComposerJson();
            $this->updateConfigFromCli($input);

            $this->buildDependencyList();

            $this->enumerateFiles();

            $this->copyFiles();

            $this->determineChanges();

            $this->performReplacements();

            $this->performReplacementsInComposerFiles();

            $this->performReplacementsInProjectFiles();

            $this->addLicenses();

            $this->generateAutoloader();

            $this->cleanUp();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }


    
    protected function loadProjectComposerPackage(): void
    {
        $this->logger->info('Loading package...');

        $this->projectComposerPackage = new ProjectComposerPackage($this->workingDir);

        
        
    }

    protected function loadConfigFromComposerJson(): void
    {
        $this->logger->info('Loading composer.json config...');

        $this->config = $this->projectComposerPackage->getStraussConfig();
    }

    protected function updateConfigFromCli(InputInterface $input): void
    {
        $this->logger->info('Loading cli config...');

        $this->config->updateFromCli($input);
    }

    
    protected function buildDependencyList(): void
    {
        $this->logger->info('Building dependency list...');

        $this->dependenciesEnumerator = new DependenciesEnumerator(
            $this->workingDir,
            $this->config
        );
        $this->flatDependencyTree = $this->dependenciesEnumerator->getAllDependencies();

        
    }

    protected function enumerateFiles(): void
    {
        $this->logger->info('Enumerating files...');

        $fileEnumerator = new FileEnumerator(
            $this->workingDir,
            $this->config
        );

        $this->discoveredFiles = $fileEnumerator->compileFileListForDependencies($this->flatDependencyTree);
    }

    
    protected function copyFiles(): void
    {
        (new FileCopyScanner($this->workingDir, $this->config))->scanFiles($this->discoveredFiles);

        if ($this->config->getTargetDirectory() === $this->config->getVendorDirectory()) {
            
            return;
        }

        $this->logger->info('Copying files...');

        $copier = new Copier(
            $this->discoveredFiles,
            $this->workingDir,
            $this->config
        );


        $copier->prepareTarget();
        $copier->copy();
    }

    
    protected function determineChanges(): void
    {
        $this->logger->info('Determining changes...');

        $fileScanner = new FileSymbolScanner($this->config);

        $this->discoveredSymbols = $fileScanner->findInFiles($this->discoveredFiles);

        $changeEnumerator = new ChangeEnumerator(
            $this->config,
            $this->workingDir
        );
        $changeEnumerator->determineReplacements($this->discoveredSymbols);
    }

    
    
    protected function performReplacements(): void
    {
        $this->logger->info('Performing replacements...');

        $this->replacer = new Prefixer($this->config, $this->workingDir);

        $this->replacer->replaceInFiles($this->discoveredSymbols, $this->discoveredFiles->getFiles());
    }

    protected function performReplacementsInComposerFiles(): void
    {
        if ($this->config->getTargetDirectory() !== $this->config->getVendorDirectory()) {
            
            return;
        }

        $projectReplace = new Prefixer($this->config, $this->workingDir);

        $fileEnumerator = new FileEnumerator(
            $this->workingDir,
            $this->config
        );

        $files = $fileEnumerator->compileFileListForPaths([$this->config->getVendorDirectory() . 'composer']);

        $composerPhpFileRelativePaths = array_map(
            fn($file) => $file->getSourcePath($this->workingDir . $this->config->getVendorDirectory()),
            $files->getFiles()
        );

        $projectReplace->replaceInProjectFiles($this->discoveredSymbols, $composerPhpFileRelativePaths);
    }

    protected function performReplacementsInProjectFiles(): void
    {

        $callSitePaths =
            $this->config->getUpdateCallSites()
            ?? $this->projectComposerPackage->getFlatAutoloadKey();

        if (empty($callSitePaths)) {
            return;
        }

        $projectReplace = new Prefixer($this->config, $this->workingDir);

        $fileEnumerator = new FileEnumerator(
            $this->workingDir,
            $this->config
        );

        $phpFiles = $fileEnumerator->compileFileListForPaths($callSitePaths);

        $phpFilesRelativePaths = array_map(
            fn($file) => $file->getSourcePath($this->workingDir),
            $phpFiles->getFiles()
        );

        
        

        $projectReplace->replaceInProjectFiles($this->discoveredSymbols, $phpFilesRelativePaths);
    }

    protected function writeClassAliasMap(): void
    {
    }

    protected function addLicenses(): void
    {
        $this->logger->info('Adding licenses...');

        $author = $this->projectComposerPackage->getAuthor();

        $dependencies = $this->flatDependencyTree;

        $licenser = new Licenser($this->config, $this->workingDir, $dependencies, $author);

        $licenser->copyLicenses();

        $modifiedFiles = $this->replacer->getModifiedFiles();
        $licenser->addInformationToUpdatedFiles($modifiedFiles);
    }

    
    protected function generateAutoloader(): void
    {
        if ($this->config->getTargetDirectory() === $this->config->getVendorDirectory()) {
            $this->logger->info('Skipping autoloader generation as target directory is vendor directory.');
            return;
        }
        if (isset($this->projectComposerPackage->getAutoload()['classmap'])
            && in_array(
                $this->config->getTargetDirectory(),
                $this->projectComposerPackage->getAutoload()['classmap'],
                true
            )
        ) {
            $this->logger->info('Skipping autoloader generation as target directory is in Composer classmap. Run `composer dump-autoload`.');
            return;
        }

        $this->logger->info('Generating autoloader...');

        $allFilesAutoloaders = $this->dependenciesEnumerator->getAllFilesAutoloaders();
        $filesAutoloaders = array();
        foreach ($allFilesAutoloaders as $packageName => $packageFilesAutoloader) {
            if (in_array($packageName, $this->config->getExcludePackagesFromCopy())) {
                continue;
            }
            $filesAutoloaders[$packageName] = $packageFilesAutoloader;
        }

        $classmap = new Autoload($this->config, $this->workingDir, $filesAutoloaders);

        $classmap->generate();
    }

    
    protected function generateClassAliasList(): void
    {
    }

    
    protected function cleanUp(): void
    {
        if ($this->config->getTargetDirectory() === $this->config->getVendorDirectory()) {
            
            return;
        }

        $this->logger->info('Cleaning up...');

        $cleanup = new Cleanup($this->config, $this->workingDir);

        

        
        $cleanup->cleanup($this->discoveredFiles->getFiles());
    }
}
