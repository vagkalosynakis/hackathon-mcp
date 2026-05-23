<?php
declare(strict_types=1);

header('Content-Type: application/json');

echo json_encode([
    'server'  => 'Fintech MCP Server',
    'version' => '1.0.0',
    'status'  => 'ok',
    'tools'   => ['get_accounts', 'get_transactions', 'get_contacts', 'get_balance', 'get_account_transactions', 'send_money', 'pay_bill'],
]);
