<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Doctrine\Deprecations\PHPUnit;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PsMcpServerDeps\Doctrine\Deprecations\Deprecation;

trait VerifyDeprecations
{
    private $doctrineDeprecationsExpectations = [];

    private $doctrineNoDeprecationsExpectations = [];

    public function expectDeprecationWithIdentifier(string $identifier): void
    {
        $this->doctrineDeprecationsExpectations[$identifier] = Deprecation::getTriggeredDeprecations()[$identifier] ?? 0;
    }

    public function expectNoDeprecationWithIdentifier(string $identifier): void
    {
        $this->doctrineNoDeprecationsExpectations[$identifier] = Deprecation::getTriggeredDeprecations()[$identifier] ?? 0;
    }

    #[Before]
    public function enableDeprecationTracking(): void
    {
        Deprecation::enableTrackingDeprecations();
    }

    #[After]
    public function verifyDeprecationsAreTriggered(): void
    {
        foreach ($this->doctrineDeprecationsExpectations as $identifier => $expectation) {
            $actualCount = Deprecation::getTriggeredDeprecations()[$identifier] ?? 0;

            $this->assertTrue(
                $actualCount > $expectation,
                \sprintf(
                    "Expected deprecation with identifier '%s' was not triggered by code executed in test.",
                    $identifier
                )
            );
        }

        foreach ($this->doctrineNoDeprecationsExpectations as $identifier => $expectation) {
            $actualCount = Deprecation::getTriggeredDeprecations()[$identifier] ?? 0;

            $this->assertTrue(
                $actualCount === $expectation,
                \sprintf(
                    "Deprecation with identifier '%s' was triggered by code executed in test, but expected not to.",
                    $identifier
                )
            );
        }
    }
}
