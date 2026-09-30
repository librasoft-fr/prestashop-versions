<?php



if (\PHP_VERSION_ID >= 80000) {
    return require __DIR__.'/bootstrap80.php';
}

return require __DIR__.'/bootstrap72.php';
