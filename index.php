<?php
declare(strict_types=1);

header('Content-Type: application/json');

echo json_encode([
    'server'  => 'Fintech MCP Server',
    'version' => '1.0.0',
    'status'  => 'ok',
    'tools'   => ['get_balance', 'send_money', 'get_transactions', 'pay_bill'],
]);
