<?php

$straussAutoload = __DIR__ . '/vendor-prefixed/autoload.php';
if (file_exists($straussAutoload)) {
    require_once $straussAutoload;
}
