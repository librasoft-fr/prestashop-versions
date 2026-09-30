<?php



namespace Symfony\Component\Process\Pipes;

use Symfony\Component\Process\Exception\InvalidArgumentException;


abstract class AbstractPipes implements PipesInterface
{
    public array $pipes = [];

    private string $inputBuffer = '';
    
    private $input;
    private bool $blocked = true;
    private ?string $lastError = null;

    
    public function __construct($input)
    {
        if (\is_resource($input) || $input instanceof \Iterator) {
            $this->input = $input;
        } else {
            $this->inputBuffer = (string) $input;
        }
    }

    public function close(): void
    {
        foreach ($this->pipes as $pipe) {
            if (\is_resource($pipe)) {
                fclose($pipe);
            }
        }
        $this->pipes = [];
    }

    
    protected function hasSystemCallBeenInterrupted(): bool
    {
        $lastError = $this->lastError;
        $this->lastError = null;

        if (null === $lastError) {
            return false;
        }

        if (false !== stripos($lastError, 'interrupted system call')) {
            return true;
        }

        
        
        
        return \defined('SOCKET_EINTR') && str_starts_with($lastError, 'stream_select(): Unable to select ['.\SOCKET_EINTR.']');
    }

    
    protected function unblock(): void
    {
        if (!$this->blocked) {
            return;
        }

        foreach ($this->pipes as $pipe) {
            stream_set_blocking($pipe, false);
        }
        if (\is_resource($this->input)) {
            stream_set_blocking($this->input, false);
        }

        $this->blocked = false;
    }

    
    protected function write(): ?array
    {
        if (!isset($this->pipes[0])) {
            return null;
        }
        $input = $this->input;

        if ($input instanceof \Iterator) {
            if (!$input->valid()) {
                $input = null;
            } elseif (\is_resource($input = $input->current())) {
                stream_set_blocking($input, false);
            } elseif (!isset($this->inputBuffer[0])) {
                if (!\is_string($input)) {
                    if (!\is_scalar($input)) {
                        throw new InvalidArgumentException(\sprintf('"%s" yielded a value of type "%s", but only scalars and stream resources are supported.', get_debug_type($this->input), get_debug_type($input)));
                    }
                    $input = (string) $input;
                }
                $this->inputBuffer = $input;
                $this->input->next();
                $input = null;
            } else {
                $input = null;
            }
        }

        $r = $e = [];
        $w = [$this->pipes[0]];

        
        if (false === @stream_select($r, $w, $e, 0, 0)) {
            return null;
        }

        foreach ($w as $stdin) {
            if (isset($this->inputBuffer[0])) {
                if (false === $written = @fwrite($stdin, $this->inputBuffer)) {
                    return $this->closeBrokenInputPipe();
                }
                $this->inputBuffer = substr($this->inputBuffer, $written);
                if (isset($this->inputBuffer[0]) && isset($this->pipes[0])) {
                    return [$this->pipes[0]];
                }
            }

            if ($input) {
                while (true) {
                    $data = fread($input, self::CHUNK_SIZE);
                    if (!isset($data[0])) {
                        break;
                    }
                    if (false === $written = @fwrite($stdin, $data)) {
                        return $this->closeBrokenInputPipe();
                    }
                    $data = substr($data, $written);
                    if (isset($data[0])) {
                        $this->inputBuffer = $data;

                        return isset($this->pipes[0]) ? [$this->pipes[0]] : null;
                    }
                }
                if (feof($input)) {
                    if ($this->input instanceof \Iterator) {
                        $this->input->next();
                    } else {
                        $this->input = null;
                    }
                }
            }
        }

        
        if (!isset($this->inputBuffer[0]) && !($this->input instanceof \Iterator ? $this->input->valid() : $this->input)) {
            $this->input = null;
            fclose($this->pipes[0]);
            unset($this->pipes[0]);
        } elseif (!$w) {
            return [$this->pipes[0]];
        }

        return null;
    }

    private function closeBrokenInputPipe(): void
    {
        $this->lastError = error_get_last()['message'] ?? null;
        if (\is_resource($this->pipes[0] ?? null)) {
            fclose($this->pipes[0]);
        }
        unset($this->pipes[0]);

        $this->input = null;
        $this->inputBuffer = '';
    }

    
    public function handleError(int $type, string $msg): void
    {
        $this->lastError = $msg;
    }
}
