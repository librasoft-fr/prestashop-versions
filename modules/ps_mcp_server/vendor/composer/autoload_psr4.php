<?php



$vendorDir = dirname(__DIR__);
$baseDir = dirname($vendorDir);

return array(
    'phpDocumentor\\Reflection\\' => array($vendorDir . '/phpdocumentor/reflection-docblock/src', $vendorDir . '/phpdocumentor/type-resolver/src', $vendorDir . '/phpdocumentor/reflection-common/src'),
    'Webmozart\\Assert\\' => array($vendorDir . '/webmozart/assert/src'),
    'Symfony\\Polyfill\\Uuid\\' => array($vendorDir . '/symfony/polyfill-uuid'),
    'Symfony\\Polyfill\\Php80\\' => array($vendorDir . '/symfony/polyfill-php80'),
    'Symfony\\Component\\Uid\\' => array($vendorDir . '/symfony/uid'),
    'Symfony\\Component\\OptionsResolver\\' => array($vendorDir . '/symfony/options-resolver'),
    'Sentry\\' => array($vendorDir . '/sentry/sentry/src'),
    'Psr\\Log\\' => array($vendorDir . '/psr/log/src'),
    'Psr\\Http\\Server\\' => array($vendorDir . '/psr/http-server-middleware/src', $vendorDir . '/psr/http-server-handler/src'),
    'Psr\\Http\\Message\\' => array($vendorDir . '/psr/http-factory/src', $vendorDir . '/psr/http-message/src'),
    'Psr\\Http\\Client\\' => array($vendorDir . '/psr/http-client/src'),
    'Psr\\EventDispatcher\\' => array($vendorDir . '/psr/event-dispatcher/src'),
    'Psr\\Container\\' => array($vendorDir . '/psr/container/src'),
    'Psr\\Clock\\' => array($vendorDir . '/psr/clock/src'),
    'Prestashop\\ModuleLibMboInstaller\\' => array($vendorDir . '/prestashop/module-lib-mbo-installer/src'),
    'PrestaShop\\Module\\PsMcpServer\\' => array($baseDir . '/src'),
    'PrestaShop\\ModuleLibServiceContainer\\' => array($vendorDir . '/prestashop/module-lib-service-container/src'),
    'PrestaShop\\ModuleLibCacheDirectoryProvider\\' => array($vendorDir . '/prestashop/module-lib-cache-directory-provider/src'),
    'PHPStan\\PhpDocParser\\' => array($vendorDir . '/phpstan/phpdoc-parser/src'),
    'Opis\\Uri\\' => array($vendorDir . '/opis/uri/src'),
    'Opis\\String\\' => array($vendorDir . '/opis/string/src'),
    'Opis\\JsonSchema\\' => array($vendorDir . '/opis/json-schema/src'),
    'Monolog\\' => array($vendorDir . '/monolog/monolog/src/Monolog'),
    'Mcp\\' => array($vendorDir . '/mcp/sdk/src'),
    'Jean85\\' => array($vendorDir . '/jean85/pretty-package-versions/src'),
    'Http\\Discovery\\' => array($vendorDir . '/php-http/discovery/src'),
    'GuzzleHttp\\Psr7\\' => array($vendorDir . '/guzzlehttp/psr7/src'),
    'Doctrine\\Deprecations\\' => array($vendorDir . '/doctrine/deprecations/src'),
);
