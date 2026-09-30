<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Jean85;

use Composer\InstalledVersions;
use PsMcpServerDeps\Jean85\Exception\ProvidedPackageException;
use PsMcpServerDeps\Jean85\Exception\ReplacedPackageException;

class PrettyVersions
{
    public static function getVersion(string $packageName): Version
    {
        self::checkProvidedPackages($packageName);

        self::checkReplacedPackages($packageName);

        return new Version(
            $packageName,
            InstalledVersions::getPrettyVersion($packageName),
            InstalledVersions::getReference($packageName)
        );
    }

    public static function getRootPackageName(): string
    {
        return InstalledVersions::getRootPackage()['name'];
    }

    public static function getRootPackageVersion(): Version
    {
        return new Version(
            self::getRootPackageName(),
            InstalledVersions::getRootPackage()['pretty_version'],
            InstalledVersions::getRootPackage()['reference']
        );
    }

    protected static function checkProvidedPackages(string $packageName): void
    {
        foreach (InstalledVersions::getAllRawData() as $installed) {
            if (isset($installed['versions'][$packageName]['provided'])) {
                throw ProvidedPackageException::create($packageName);
            }
        }
    }

    protected static function checkReplacedPackages(string $packageName): void
    {
        foreach (InstalledVersions::getAllRawData() as $installed) {
            if (isset($installed['versions'][$packageName]['replaced'])) {
                throw ReplacedPackageException::create($packageName);
            }
        }
    }
}
