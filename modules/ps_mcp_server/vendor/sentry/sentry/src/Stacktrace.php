<?php

declare(strict_types=1);

namespace Sentry;


final class Stacktrace
{
    
    private $frames = [];

    
    public function __construct(array $frames)
    {
        if (empty($frames)) {
            throw new \InvalidArgumentException('Expected a non empty list of frames.');
        }

        foreach ($frames as $frame) {
            if (!$frame instanceof Frame) {
                throw new \UnexpectedValueException(\sprintf('Expected an instance of the "%s" class. Got: "%s".', Frame::class, get_debug_type($frame)));
            }
        }

        $this->frames = $frames;
    }

    
    public function getFrames(): array
    {
        return $this->frames;
    }

    
    public function getFrame(int $index): Frame
    {
        if ($index < 0 || $index >= \count($this->frames)) {
            throw new \OutOfBoundsException();
        }

        return $this->frames[$index];
    }

    
    public function addFrame(Frame $frame): self
    {
        array_unshift($this->frames, $frame);

        return $this;
    }

    
    public function removeFrame(int $index): self
    {
        if (!isset($this->frames[$index])) {
            throw new \OutOfBoundsException(\sprintf('Cannot remove the frame at index %d.', $index));
        }

        if (\count($this->frames) === 1) {
            throw new \RuntimeException('Cannot remove all frames from the stacktrace.');
        }

        array_splice($this->frames, $index, 1);

        return $this;
    }
}
