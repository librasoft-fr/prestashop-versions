<?php

declare(strict_types=1);

namespace Sentry\Integration;

use Composer\InstalledVersions;
use Jean85\PrettyVersions;
use Sentry\Event;
use Sentry\SentrySdk;
use Sentry\State\Scope;


final class ModulesIntegration implements IntegrationInterface
{
    
    private static $packages = [];

    
    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(static function (Event $event): Event {
            $integration = SentrySdk::getCurrentHub()->getIntegration(self::class);

            
            
            if ($integration !== null) {
                $event->setModules(self::getComposerPackages());
            }

            return $event;
        });
    }

    
    private static function getComposerPackages(): array
    {
        if (empty(self::$packages)) {
            foreach (self::getInstalledPackages() as $package) {
                try {
                    self::$packages[$package] = PrettyVersions::getVersion($package)->getPrettyVersion();
                } catch (\Throwable $exception) {
                    continue;
                }
            }
        }

        return self::$packages;
    }

    
    private static function getInstalledPackages(): array
    {
        if (class_exists(InstalledVersions::class)) {
            return InstalledVersions::getInstalledPackages();
        }

        $versionsClass = 'PackageVersions\\Versions';

        if (class_exists($versionsClass)) {
            
            
            $versions = \constant($versionsClass . '::VERSIONS');

            if (\is_array($versions)) {
                
                $packages = array_keys($versions);

                return $packages;
            }
        }

        
        return ['sentry/sentry'];
    }
}
