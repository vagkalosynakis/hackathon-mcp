## MODIFIED Requirements

### Requirement: Get Account Balance
The system SHALL expose a `get_balance` MCP tool that returns the current balance and currency for a given account identifier.

The tool MUST accept a single required parameter `accountId` (string) and return a structured response containing at minimum: `accountId`, `balance` (numeric), `currency` (ISO 4217 string), and `asOf` (ISO 8601 datetime string).

When the backend is unreachable, the tool MUST return a Faker-generated dummy payload. The `accountId` field MUST echo the input argument exactly. The `balance` MUST be a randomly generated positive float. The `asOf` MUST be a current ISO 8601 datetime string. The dummy response MUST vary between calls so the demo does not appear static.

#### Scenario: Successful balance retrieval
- **WHEN** `get_balance` is called with a valid `accountId`
- **THEN** the tool returns a JSON object containing `accountId`, `balance`, `currency`, and `asOf`

#### Scenario: Backend unreachable — dummy varies per call
- **WHEN** `get_balance` is called and the backend is unavailable
- **THEN** the tool returns a Faker-generated payload where `accountId` matches the input, `balance` is a random positive float, and `asOf` is a current timestamp

---

### Requirement: Send Money
The system SHALL expose a `send_money` MCP tool that initiates a money transfer from one account to another.

The tool MUST accept the following parameters:
- `fromAccountId` (string, required) — source account identifier
- `toAccountId` (string, required) — destination account identifier
- `amount` (float, required) — positive transfer amount; MUST be greater than zero
- `currency` (string, required) — ISO 4217 currency code
- `reference` (string, optional) — free-text payment reference

When the backend is unreachable, the tool MUST return a Faker-generated dummy payload. All input arguments MUST be echoed back verbatim. The `transferId` MUST be a UUID. The `status` MUST be randomly either `"pending"` or `"completed"`. The `createdAt` MUST be a current ISO 8601 datetime string.

#### Scenario: Successful transfer initiation
- **WHEN** `send_money` is called with valid `fromAccountId`, `toAccountId`, `amount`, and `currency`
- **THEN** the tool returns a confirmation object with a `transferId` and `status`

#### Scenario: Backend unreachable — dummy echoes inputs
- **WHEN** `send_money` is called and the backend is unavailable
- **THEN** the response echoes `fromAccountId`, `toAccountId`, `amount`, `currency`, and `reference` exactly as supplied, and provides a UUID `transferId` and current `createdAt`

---

### Requirement: Get Transaction History
The system SHALL expose a `get_transactions` MCP tool that returns a paginated list of past transactions for a given account.

The tool MUST accept the following parameters:
- `accountId` (string, required)
- `limit` (int, optional, default 20, max 100)
- `offset` (int, optional, default 0)
- `fromDate` (string, optional) — ISO 8601 date
- `toDate` (string, optional) — ISO 8601 date

When the backend is unreachable, the tool MUST return a Faker-generated dummy payload. The `accountId`, `limit`, and `offset` fields MUST echo the input arguments. The `transactions` array MUST contain exactly `limit` entries (capped at 10 for demo sanity). Each transaction MUST have: a UUID `transactionId`, a random `type` of `"debit"` or `"credit"`, a random positive `amount`, a `currency`, a realistic human-readable `description`, and a `date` within the past 90 days. The `total` MUST be a plausible integer greater than or equal to `limit`.

#### Scenario: Default pagination — dummy
- **WHEN** `get_transactions` is called with only `accountId` and the backend is unavailable
- **THEN** up to 20 Faker-generated transactions are returned with `offset: 0` and `accountId` echoed back

#### Scenario: Paginated request — dummy
- **WHEN** `get_transactions` is called with `limit: 5` and the backend is unavailable
- **THEN** exactly 5 Faker-generated transactions are returned

#### Scenario: Successful retrieval from backend
- **WHEN** `get_transactions` is called and the backend responds
- **THEN** the backend response is returned as-is

---

### Requirement: Pay Bill
The system SHALL expose a `pay_bill` MCP tool that submits a payment to a registered biller on behalf of an account.

The tool MUST accept the following parameters:
- `accountId` (string, required)
- `billerId` (string, required)
- `amount` (float, required) — must be greater than zero
- `currency` (string, required) — ISO 4217 currency code
- `reference` (string, optional)

When the backend is unreachable, the tool MUST return a Faker-generated dummy payload. All input arguments MUST be echoed back verbatim. The `paymentId` MUST be a UUID. The `status` MUST be randomly either `"pending"` or `"completed"`. The `createdAt` MUST be a current ISO 8601 datetime string.

#### Scenario: Successful bill payment — dummy echoes inputs
- **WHEN** `pay_bill` is called and the backend is unavailable
- **THEN** the response echoes `accountId`, `billerId`, `amount`, `currency`, and `reference` exactly as supplied, and provides a UUID `paymentId` and current `createdAt`

#### Scenario: Successful bill payment from backend
- **WHEN** `pay_bill` is called and the backend responds
- **THEN** the backend response is returned as-is
