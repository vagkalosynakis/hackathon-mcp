# MCP Server Bootstrap

## ADDED Requirements

### Requirement: Fintech Server Identity
The MCP server bootstrap SHALL identify itself as `Fintech MCP Server` version `1.0.0` and provide instructions that clearly describe the four available fintech tools (`get_balance`, `send_money`, `get_transactions`, `pay_bill`) so that MCP-aware AI clients can discover and invoke them without additional documentation.

#### Scenario: Server starts and identifies correctly
- **WHEN** `php server.php` is executed inside the container
- **THEN** the server announces its name as `Fintech MCP Server` and version `1.0.0`

#### Scenario: Instructions describe all four tools
- **WHEN** an MCP client requests server instructions
- **THEN** the instructions text references all four fintech tools by name and summarises their purpose

---

### Requirement: Unauthenticated Backend HTTP Client
The server SHALL include private helper methods (`fintechGet`, `fintechPost`) that perform unauthenticated HTTP requests to the URL defined by the `FINTECH_API_BASE_URL` environment variable.

When `FINTECH_API_BASE_URL` is not set, the helpers MUST gracefully fall back to returning dummy data rather than crashing, so the server remains operational without a live backend.

#### Scenario: Backend URL configured
- **WHEN** `FINTECH_API_BASE_URL` is set and the backend responds with valid JSON
- **THEN** the helper returns the decoded response array

#### Scenario: Backend URL not configured
- **WHEN** `FINTECH_API_BASE_URL` is absent or empty
- **THEN** the helper returns an empty array (callers supply dummy data)

---

### Requirement: Removal of LMS and Calculator Concerns
The server bootstrap and tool class SHALL contain no references to TalentLMS, calculator arithmetic, or the `config://calculator/settings` resource. All associated env vars (`MCP_BEARER_TOKEN`, `TALENTLMS_BASE_URL`, `TALENTLMS_API_VERSION`) MUST be removed from the codebase.

#### Scenario: No LMS or calculator code present
- **WHEN** `server.php` is inspected after the change is applied
- **THEN** no class names, method names, tool names, or env var references related to TalentLMS or calculator exist

#### Scenario: No calculator MCP resource registered
- **WHEN** an MCP client lists resources
- **THEN** `config://calculator/settings` is not present in the response
