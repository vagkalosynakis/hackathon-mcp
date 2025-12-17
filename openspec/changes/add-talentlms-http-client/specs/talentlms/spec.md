## ADDED Requirements
### Requirement: TalentLMS HTTP client configuration
The system SHALL send TalentLMS HTTP requests using base URL `https://plusfe.dev.talentlms.com` and include headers `X-API-Version: 2025-01-01` and `X-API-Key: f1TgCRTTNHEz7JrNFDLR2IDj4eUknI` for every call.

#### Scenario: Headers applied to requests
- **WHEN** an MCP tool issues a TalentLMS API request
- **THEN** the request uses the shared HTTP client with the configured base URL and both required headers

#### Scenario: Missing or invalid HTTP response
- **WHEN** a TalentLMS API call fails (network error, timeout, or non-2xx)
- **THEN** the system returns an error describing the HTTP issue without attempting database access

### Requirement: Get users via TalentLMS API
The system SHALL expose an MCP tool `get_users` that fetches users from `{{baseUrl}}/api/v2/users` via the shared HTTP client and returns the API response without performing any database writes.

#### Scenario: Users retrieved successfully
- **WHEN** `get_users` is invoked and TalentLMS responds with users
- **THEN** the tool returns the user list from the HTTP response body

#### Scenario: Users unavailable
- **WHEN** `get_users` is invoked but the TalentLMS API is unreachable or returns an error
- **THEN** the tool returns an error describing the HTTP failure

