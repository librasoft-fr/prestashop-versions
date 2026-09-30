<?php



namespace Mcp\Server\Session;

use Mcp\Server\NativeClock;
use Psr\Clock\ClockInterface;
use Symfony\Component\Uid\Uuid;


class FileSessionStore implements SessionStoreInterface
{
    public function __construct(
        private readonly string $directory,
        private readonly int $ttl = 3600,
        private readonly ClockInterface $clock = new NativeClock(),
    ) {
        if (!is_dir($this->directory)) {
            @mkdir($this->directory, 0775, true);
        }

        if (!is_dir($this->directory) || !is_writable($this->directory)) {
            throw new \RuntimeException(\sprintf('Session directory "%s" is not writable.', $this->directory));
        }
    }

    public function exists(Uuid $id): bool
    {
        $path = $this->pathFor($id);

        if (!is_file($path)) {
            return false;
        }

        $mtime = @filemtime($path) ?: 0;

        return ($this->clock->now()->getTimestamp() - $mtime) <= $this->ttl;
    }

    public function read(Uuid $id): string|false
    {
        $path = $this->pathFor($id);

        if (!is_file($path)) {
            return false;
        }

        $mtime = @filemtime($path) ?: 0;
        if (($this->clock->now()->getTimestamp() - $mtime) > $this->ttl) {
            @unlink($path);

            return false;
        }

        $data = @file_get_contents($path);
        if (false === $data) {
            return false;
        }

        return $data;
    }

    public function write(Uuid $id, string $data): bool
    {
        $path = $this->pathFor($id);

        $tmp = $path.'.tmp';
        if (false === @file_put_contents($tmp, $data, \LOCK_EX)) {
            return false;
        }

        
        if (!@rename($tmp, $path)) {
            
            if (false === @copy($tmp, $path)) {
                @unlink($tmp);

                return false;
            }
            @unlink($tmp);
        }

        @touch($path, $this->clock->now()->getTimestamp());

        return true;
    }

    public function destroy(Uuid $id): bool
    {
        $path = $this->pathFor($id);

        if (is_file($path)) {
            @unlink($path);
        }

        return true;
    }

    
    public function gc(): array
    {
        $deleted = [];
        $now = $this->clock->now()->getTimestamp();

        $dir = @opendir($this->directory);
        if (false === $dir) {
            return $deleted;
        }

        while (($entry = readdir($dir)) !== false) {
            
            if ('.' === $entry || '..' === $entry) {
                continue;
            }

            $path = $this->directory.\DIRECTORY_SEPARATOR.$entry;
            if (!is_file($path)) {
                continue;
            }

            $mtime = @filemtime($path) ?: 0;
            if (($now - $mtime) > $this->ttl) {
                @unlink($path);
                try {
                    $deleted[] = Uuid::fromString($entry);
                } catch (\Throwable) {
                    
                }
            }
        }

        closedir($dir);

        return $deleted;
    }

    private function pathFor(Uuid $id): string
    {
        return $this->directory.\DIRECTORY_SEPARATOR.$id->toRfc4122();
    }
}
