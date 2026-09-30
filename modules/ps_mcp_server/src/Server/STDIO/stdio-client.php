<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */
$cmd = 'php ./stdio-server.php';

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['file', 'php://stderr', 'a'],
];

$process = proc_open($cmd, $descriptors, $pipes);

if (is_resource($process)) {
    $initRequest = json_encode([
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [],
            'clientInfo' => ['name' => 'test-client', 'version' => '1.0'],
        ],
    ]);

    fwrite($pipes[0], $initRequest . "\n");

    $response = fgets($pipes[1]);
    echo 'Init Response: ' . $response . "\n";

    fwrite($pipes[0], json_encode([
        'jsonrpc' => '2.0',
        'method' => 'notifications/initialized',
    ]) . "\n");

    $toolsRequest = json_encode([
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/list',
    ]);

    fwrite($pipes[0], $toolsRequest . "\n");

    $toolsResponse = fgets($pipes[1]);
    echo 'Tools list: ' . $toolsResponse . "\n";

    fclose($pipes[0]);
    fclose($pipes[1]);
    proc_close($process);
}
