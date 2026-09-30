<?php

namespace PsMcpServerDeps\Http\Discovery\Exception;

use PsMcpServerDeps\Http\Discovery\Exception;

final class NoCandidateFoundException extends \Exception implements Exception
{
    public function __construct($strategy, array $candidates)
    {
        $classes = array_map(
            function ($a) {
                return $a['class'];
            },
            $candidates
        );

        $message = sprintf(
            'No valid candidate found using strategy "%s". We tested the following candidates: %s.',
            $strategy,
            implode(', ', array_map([$this, 'stringify'], $classes))
        );

        parent::__construct($message);
    }

    private function stringify($mixed)
    {
        if (is_string($mixed)) {
            return $mixed;
        }

        if (is_array($mixed) && 2 === count($mixed)) {
            return sprintf('%s::%s', $this->stringify($mixed[0]), $mixed[1]);
        }

        return is_object($mixed) ? get_class($mixed) : gettype($mixed);
    }
}
