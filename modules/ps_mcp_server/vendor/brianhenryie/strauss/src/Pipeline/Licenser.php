<?php


namespace BrianHenryIE\Strauss\Pipeline;

use BrianHenryIE\Strauss\Composer\ComposerPackage;
use BrianHenryIE\Strauss\Composer\Extra\StraussConfig;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Finder\Finder;

class Licenser
{
    protected string $workingDir;

    protected string $vendorDir;

    
    protected array $dependencies;

    
    protected string $author;

    protected string $targetDirectory;

    protected bool $includeModifiedDate;

    
    protected bool $includeAuthor = true;

    
    protected array $discoveredLicenseFiles = array();

    
    protected $filesystem;

    
    public function __construct(StraussConfig $config, string $workingDir, array $dependencies, string $author)
    {
        $this->workingDir = $workingDir;
        $this->dependencies = $dependencies;
        $this->author = $author;

        $this->targetDirectory = $config->getTargetDirectory();
        $this->vendorDir = $config->getVendorDirectory();
        $this->includeModifiedDate = $config->isIncludeModifiedDate();
        $this->includeAuthor = $config->isIncludeAuthor();

        $this->filesystem = new Filesystem(new LocalFilesystemAdapter('/'));
    }

    public function copyLicenses(): void
    {
        $this->findLicenseFiles();

        foreach ($this->getDiscoveredLicenseFiles() as $licenseFile) {
            $targetLicenseFile = $this->targetDirectory . $licenseFile;

            $targetLicenseFileDir = dirname($targetLicenseFile);

            
            if ($this->filesystem->fileExists($targetLicenseFile)) {
                continue;
            }

            
            if (! is_dir($targetLicenseFileDir)) {
                continue;
            }

            $this->filesystem->copy(
                $this->vendorDir . $licenseFile,
                $targetLicenseFile
            );
        }
    }

    
    public function findLicenseFiles(?Finder $finder = null): void
    {
        
        $finder = $finder ?? new Finder();

        
        foreach ($this->dependencies as $dependency) {
            $packagePath = $dependency->getPackageAbsolutePath();

            
            
            
            $finder->files()->in($packagePath)->followLinks()->exclude(array( 'vendor' ))->name('/^.*licen.e.*/i');

            
            foreach ($finder as $foundFile) {
                $filePath = $foundFile->getPathname();

                
                $filePath = preg_replace('#[\\\/]+#', DIRECTORY_SEPARATOR, $filePath);

                $this->discoveredLicenseFiles[$filePath] = $dependency->getPackageName();
            }
        }
    }
    
    public function getDiscoveredLicenseFiles(): array
    {
        return array_keys($this->discoveredLicenseFiles);
    }

    
    public function addInformationToUpdatedFiles(array $modifiedFiles): void
    {
        
        $date = gmdate("d-F-Y", time());

        foreach ($modifiedFiles as $relativeFilePath => $package) {
            $filepath = $this->workingDir . $this->targetDirectory . $relativeFilePath;

            if (!$this->filesystem->fileExists($filepath)) {
                continue;
            }

            $contents = $this->filesystem->read($filepath);

            $updatedContents = $this->addChangeDeclarationToPhpString(
                $contents,
                $date,
                $package->getPackageName(),
                $package->getLicense()
            );

            if ($updatedContents !== $contents) {
                $this->filesystem->write($filepath, $updatedContents);
            }
        }
    }

    
    public function addChangeDeclarationToPhpString(
        string $phpString,
        string $modifiedDate,
        string $packageName,
        string $packageLicense
    ) : string {

        $author = $this->author;

        $licenseDeclaration = "@license {$packageLicense}";
        $modifiedDeclaration = 'Modified';
        if ($this->includeAuthor) {
            $modifiedDeclaration .= " by {$author}";
        }
        if ($this->includeModifiedDate) {
            $modifiedDeclaration .= " on {$modifiedDate}";
        }
        $straussLink = 'https://github.com/BrianHenryIE/strauss';
        $modifiedDeclaration .= " using {@see {$straussLink}}.";

        $startOfFileArray = [];
        $tokenizeString =  token_get_all($phpString);

        foreach ($tokenizeString as $token) {
            if (is_array($token) && !in_array($token[1], ['namespace', '/*', ' /*'])) {
                $startOfFileArray[] = $token[1];
                $token = array_shift($tokenizeString);

                if (is_array($token) && stristr($token[1], 'strauss')) {
                    return $phpString;
                }
            } elseif (!is_array($token)) {
                $startOfFileArray[] = $token;
            }
        }
        
        
        $startOfFile = implode('', $startOfFileArray);

        
        $noCommentBetweenPhpOpenAndFirstCodePattern = '~<\?php[\s\n]*[\w\\\?]+~';

        $multilineCommentCapturePattern = '
            ~                        # Start the pattern
            (
            <\?php[\S\s]*            #  match the beginning of the files php-open and following whitespace
            )
            (
            \*[\S\s.]*               # followed by a multiline-comment-open
            )
            (
            \*/                      # Capture the multiline-comment-close separately
            )
            ~Ux';                          

        $replaceInMultilineCommentFunction = function ($matches) use (
            $licenseDeclaration,
            $modifiedDeclaration
        ) {
            
            $commentLines = explode("\n", $matches[2]);

            if (isset($commentLines[1])&& 1 === preg_match('/^([\s\\\*]*)/', $commentLines[1], $output_array)) {
                $lineStart = $output_array[1];
            } else {
                $lineStart = ' * ';
            }

            $appendString = "*\n";

            
            if (false === strpos($matches[2], 'licen')) {
                $appendString .= "{$lineStart}{$licenseDeclaration}\n";
            }

            $appendString .= "{$lineStart}{$modifiedDeclaration}\n";

            $commentEnd =  rtrim(rtrim($lineStart, ' '), '*').'*/';

            $replaceWith = $matches[1] . $matches[2] . $appendString . $commentEnd;

            return $replaceWith;
        };

        
        if (1 === preg_match($noCommentBetweenPhpOpenAndFirstCodePattern, $phpString)) {
            $modifiedComment = "/**\n * {$licenseDeclaration}\n *\n * {$modifiedDeclaration}\n */";
            $updatedPhpString = preg_replace('~<\?php~', "<?php\n". $modifiedComment, $phpString, 1);
        } else {
            $updatedPhpString = preg_replace_callback(
                $multilineCommentCapturePattern,
                $replaceInMultilineCommentFunction,
                $phpString,
                1
            );
        }

        
        return $updatedPhpString ?? $phpString;
    }
}
