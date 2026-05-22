# Fintech Tools

## ADDED Requirements

### Requirement: Get Account Balance
The system SHALL expose a `get_balance` MCP tool that returns the current balance and currency for a given account identifier.

The tool MUST accept a single required parameter `accountId` (string) and return a structured response containing at minimum: `accountId`, `balance` (numeric), `currency` (ISO 4217 string), and `asOf` (ISO 8601 datetime string).

When the backend is unreachable, the tool MUST return a hardcoded dummy payload rather than raising an error, so that demos remain functional without a live backend.

#### Scenario: Successful balance retrieval
- **WHEN** `get_balance` is called with a valid `accountId`
- **THEN** the tool returns a JSON object containing `accountId`, `balance`, `currency`, and `asOf`

#### Scenario: Backend unreachable
- **WHEN** `get_balance` is called and `FINTECH_API_BASE_URL` is not set or the backend is down
- **THEN** the tool returns a hardcoded dummy balance payload (no exception thrown)

---

### Requirement: Send Money
The system SHALL expose a `send_money` MCP tool that initiates a money transfer from one account to another.

The tool MUST accept the following parameters:
- `fromAccountId` (string, required) — source account identifier
- `toAccountId` (string, required) — destination account identifier
- `amount` (float, required) — positive transfer amount; MUST be greater than zero
- `currency` (string, required) — ISO 4217 currency code (e.g. `EUR`, `USD`)
- `reference` (string, optional) — free-text payment reference visible to both parties

The tool MUST return a structured response containing at minimum: `transferId`, `status`, `fromAccountId`, `toAccountId`, `amount`, `currency`, and `createdAt`.

#### Scenario: Successful transfer initiation
- **WHEN** `send_money` is called with valid `fromAccountId`, `toAccountId`, `amount`, and `currency`
- **THEN** the tool returns a confirmation object with a `transferId` and `status` of `"pending"` or `"completed"`

#### Scenario: Optional reference included
- **WHEN** `send_money` is called with an optional `reference` string
- **THEN** the returned confirmation object includes the `reference` field

#### Scenario: Backend unreachable
- **WHEN** `send_money` is called and the backend is unavailable
- **THEN** the tool returns a hardcoded dummy confirmation payload (no exception thrown)

---

### Requirement: Get Transaction History
The system SHALL expose a `get_transactions` MCP tool that returns a paginated list of past transactions for a given account.

The tool MUST accept the following parameters:
- `accountId` (string, required) — the account whose transactions to retrieve
- `limit` (int, optional, default 20, max 100) — number of transactions to return
- `offset` (int, optional, default 0) — zero-based offset for pagination
- `fromDate` (string, optional) — ISO 8601 date string; only transactions on or after this date are returned
- `toDate` (string, optional) — ISO 8601 date string; only transactions on or before this date are returned

The tool MUST return a structured response containing: `accountId`, `total` (int), `offset`, `limit`, and `transactions` (array). Each transaction item MUST include: `transactionId`, `type` (`"debit"` or `"credit"`), `amount`, `currency`, `description`, and `date`.

#### Scenario: Default pagination
- **WHEN** `get_transactions` is called with only `accountId`
- **THEN** up to 20 transactions are returned with `offset: 0`

#### Scenario: Paginated request
- **WHEN** `get_transactions` is called with `limit: 5` and `offset: 10`
- **THEN** at most 5 transactions are returned starting from position 10

#### Scenario: Date-filtered request
- **WHEN** `get_transactions` is called with `fromDate` and/or `toDate`
- **THEN** only transactions within the specified date range are returned

#### Scenario: Backend unreachable
- **WHEN** `get_transactions` is called and the backend is unavailable
- **THEN** the tool returns a hardcoded dummy transaction list (no exception thrown)

---

### Requirement: Pay Bill
The system SHALL expose a `pay_bill` MCP tool that submits a payment to a registered biller on behalf of an account.

The tool MUST accept the following parameters:
- `accountId` (string, required) — the account from which the payment is debited
- `billerId` (string, required) — identifier of the biller (e.g. utility company, telecom)
- `amount` (float, required) — payment amount; MUST be greater than zero
- `currency` (string, required) — ISO 4217 currency code
- `reference` (string, optional) — customer reference or invoice number for the biller

The tool MUST return a structured response containing at minimum: `paymentId`, `status`, `accountId`, `billerId`, `amount`, `currency`, and `createdAt`.

#### Scenario: Successful bill payment
- **WHEN** `pay_bill` is called with valid `accountId`, `billerId`, `amount`, and `currency`
- **THEN** the tool returns a confirmation object with a `paymentId` and `status` of `"pending"` or `"completed"`

#### Scenario: Optional reference included
- **WHEN** `pay_bill` is called with an optional `reference` string
- **THEN** the returned confirmation object includes the `reference` field

#### Scenario: Backend unreachable
- **WHEN** `pay_bill` is called and the backend is unavailable
- **THEN** the tool returns a hardcoded dummy payment confirmation payload (no exception thrown)
