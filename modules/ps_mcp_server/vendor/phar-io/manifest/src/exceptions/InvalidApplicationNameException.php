<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use InvalidArgumentException;

class InvalidApplicationNameException extends InvalidArgumentException implements Exception {
    public const InvalidFormat = 2;
}
