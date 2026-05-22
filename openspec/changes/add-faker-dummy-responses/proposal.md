# Change: Add Faker-backed dummy responses to fintech tools

## Why
The current hardcoded dummy payloads return the same static values every time (balance always `4250.75`, transactions always the same three rows, transfer IDs derived from a simple md5). For a demo this looks unconvincing. Replacing the static stubs with `fakerphp/faker` produces realistic, varied data on every call — IDs look like real IDs, dates vary, names and descriptions change — while still echoing back the exact arguments the caller supplied (accountId, amount, currency, etc.).

## What Changes
- Add `fakerphp/faker` as a Composer dependency (`require`, not `require-dev` — the server runs in the container)
- Replace every hardcoded `// DUMMY` return block in `server.php` with a Faker-generated payload
- Each tool echoes its input arguments back verbatim in the response and fills generated fields (IDs, dates, statuses) with Faker values
- The `fintechGet` / `fintechPost` HTTP helpers are unchanged — Faker is only used in the fallback branch when the backend is unavailable

## Tool-by-tool dummy payload changes

| Tool | Arguments echoed back | Faker-generated fields |
|---|---|---|
| `get_balance` | `accountId` | `balance` (randomFloat 2..10000), `currency` (from input or random ISO), `asOf` (ISO 8601 datetime) |
| `send_money` | `fromAccountId`, `toAccountId`, `amount`, `currency`, `reference` | `transferId` (uuid), `status` (`pending`\|`completed`), `createdAt` (ISO 8601 datetime) |
| `get_transactions` | `accountId`, `limit`, `offset` | `total` (random int), each transaction: `transactionId` (uuid), `type` (debit/credit), `amount`, `currency`, `description` (realistic), `date` (recent past) |
| `pay_bill` | `accountId`, `billerId`, `amount`, `currency`, `reference` | `paymentId` (uuid), `status` (`pending`\|`completed`), `createdAt` (ISO 8601 datetime) |

## Impact
- Affected specs: `fintech-tools` (MODIFIED — dummy response contract)
- Affected code: `composer.json`, `composer.lock`, `server.php`
- No changes to tool signatures, `#[Schema]` annotations, or HTTP helpers
