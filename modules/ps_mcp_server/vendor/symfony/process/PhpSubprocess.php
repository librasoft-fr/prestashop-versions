<?php



namespace Symfony\Component\Process;

use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\RuntimeException;


class PhpSubprocess extends Process
{
    
    public function __construct(array $command, ?string $cwd = null, ?array $env = null, int $timeout = 60, ?array $php = null)
    {
        if (null === $php) {
            $executableFinder = new PhpExecutableFinder();
            $php = $executableFinder->find(false);
            $php = false === $php ? null : array_merge([$php], $executableFinder->findArguments());
        }

        if (null === $php) {
            throw new RuntimeException('Unable to find PHP binary.');
        }

        $tmpIni = $this->writeTmpIni($this->getAllIniFiles(), sys_get_temp_dir());

        $php = array_merge($php, ['-n', '-c', $tmpIni]);
        register_shutdown_function('unlink', $tmpIni);

        $command = array_merge($php, $command);

        parent::__construct($command, $cwd, $env, null, $timeout);
    }

    public static function fromShellCommandline(string $command, ?string $cwd = null, ?array $env = null, mixed $input = null, ?float $timeout = 60): static
    {
        throw new LogicException(\sprintf('The "%s()" method cannot be called when using "%s".', __METHOD__, self::class));
    }

    public function start(?callable $callback = null, array $env = []): void
    {
        if (null === $this->getCommandLine()) {
            throw new RuntimeException('Unable to find the PHP executable.');
        }

        parent::start($callback, $env);
    }

    private function writeTmpIni(array $iniFiles, string $tmpDir): string
    {
        if (false === $tmpfile = @tempnam($tmpDir, '')) {
            throw new RuntimeException('Unable to create temporary ini file.');
        }

        
        if ('' === $iniFiles[0]) {
            array_shift($iniFiles);
        }

        $content = '';

        foreach ($iniFiles as $file) {
            
            if (($data = @file_get_contents($file)) === false) {
                throw new RuntimeException('Unable to read ini: '.$file);
            }
            
            if (preg_match('/^\s*\[(?:PATH|HOST)\s*=/mi', $data, $matches, \PREG_OFFSET_CAPTURE)) {
                $data = substr($data, 0, $matches[0][1]);
            }

            $content .= $data."\n";
        }

        
        $config = parse_ini_string($content);
        $loaded = ini_get_all(null, false);

        if (false === $config || false === $loaded) {
            throw new RuntimeException('Unable to parse ini data.');
        }

        $content .= $this->mergeLoadedConfig($loaded, $config);

        
        $content .= "opcache.enable_cli=0\n";

        if (false === @file_put_contents($tmpfile, $content)) {
            throw new RuntimeException('Unable to write temporary ini file.');
        }

        return $tmpfile;
    }

    private function mergeLoadedConfig(array $loadedConfig, array $iniConfig): string
    {
        $content = '';

        foreach ($loadedConfig as $name => $value) {
            if (!\is_string($value)) {
                continue;
            }

            if (!isset($iniConfig[$name]) || $iniConfig[$name] !== $value) {
                
                $content .= $name.'="'.addcslashes($value, '\\"')."\"\n";
            }
        }

        return $content;
    }

    private function getAllIniFiles(): array
    {
        $paths = [(string) php_ini_loaded_file()];

        if (false !== $scanned = php_ini_scanned_files()) {
            $paths = array_merge($paths, array_map('trim', explode(',', $scanned)));
        }

        return $paths;
    }
}
