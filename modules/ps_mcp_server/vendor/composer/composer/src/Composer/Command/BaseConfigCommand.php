<?php declare(strict_types=1);



namespace Composer\Command;

use Composer\Config;
use Composer\Config\JsonConfigSource;
use Composer\Json\JsonFile;
use Composer\Factory;
use Composer\Util\Platform;
use Composer\Util\Silencer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseConfigCommand extends BaseCommand
{
    
    protected $config;

    
    protected $configFile;

    
    protected $configSource;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        if ($input->getOption('global') && null !== $input->getOption('file')) {
            throw new \RuntimeException('--file and --global can not be combined');
        }

        $io = $this->getIO();
        $this->config = Factory::createConfig($io);

        
        if ($input->getOption('global')) {
            $this->config->setBaseDir($this->config->get('home'));
        }

        $configFile = $this->getComposerConfigFile($input, $this->config);

        
        if (
            ($configFile === 'composer.json' || $configFile === './composer.json')
            && !file_exists($configFile)
            && realpath(Platform::getCwd()) === realpath($this->config->get('home'))
        ) {
            file_put_contents($configFile, "{\n}\n");
        }

        $this->configFile = new JsonFile($configFile, null, $io);
        $this->configSource = new JsonConfigSource($this->configFile);

        
        if ($input->getOption('global') && !$this->configFile->exists()) {
            touch($this->configFile->getPath());
            $this->configFile->write(['config' => new \ArrayObject]);
            Silencer::call('chmod', $this->configFile->getPath(), 0600);
        }

        if (!$this->configFile->exists()) {
            throw new \RuntimeException(sprintf('File "%s" cannot be found in the current directory', $configFile));
        }
    }

    
    protected function getComposerConfigFile(InputInterface $input, Config $config): string
    {
        return $input->getOption('global')
            ? ($config->get('home') . '/config.json')
            : ($input->getOption('file') ?? Factory::getComposerFile())
        ;
    }

    
    protected function getAuthConfigFile(InputInterface $input, Config $config): string
    {
        return $input->getOption('global')
            ? ($config->get('home') . '/auth.json')
            : dirname($this->getComposerConfigFile($input, $config)) . '/auth.json'
        ;
    }
}
