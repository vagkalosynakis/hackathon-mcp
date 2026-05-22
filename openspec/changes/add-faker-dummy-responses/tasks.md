# Tasks: Add Faker-backed dummy responses

## 1. Install Faker
- [x] 1.1 Run `composer require fakerphp/faker` inside the `php-mcp` container
- [x] 1.2 Verify `fakerphp/faker` appears in `composer.json` under `require`

## 2. Update server.php — shared Faker instance
- [x] 2.1 Add a private `faker(): \Faker\Generator` method to `FintechTools` that returns a singleton `\Faker\Factory::create()` instance (lazy-init via a private property)

## 3. Replace dummy payloads tool by tool
- [x] 3.1 `getBalance`: replace static balance/currency/asOf with Faker values; echo `accountId` from input
- [x] 3.2 `sendMoney`: replace `md5(uniqid)` transferId with `$faker->uuid()`; echo all input args; use `$faker->dateTimeBetween('-1 minute', 'now')->format('c')` for `createdAt`; randomise `status` between `pending` and `completed`
- [x] 3.3 `getTransactions`: generate `$limit` transaction rows (up to 10 for demo sanity); each row uses `$faker->uuid()` for `transactionId`, random debit/credit type, random amount, realistic `description`, `$faker->dateTimeBetween('-90 days', 'now')->format('Y-m-d')` for date; echo `accountId`, `limit`, `offset` back; set `total` to a plausible int slightly larger than `limit`
- [x] 3.4 `payBill`: uuid paymentId, echo all inputs, random status, Faker createdAt

## 4. Validate
- [x] 4.1 Clear discovery cache: `rm -rf var/cache/mcp-discovery`
- [x] 4.2 Run `docker compose exec -T -w /app php-mcp php server.php` and confirm no fatal errors
- [x] 4.3 Called each tool shape via PHP and confirmed: input args echoed back, UUIDs for IDs, dates realistic, status randomised
