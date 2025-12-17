## ADDED Requirements
### Requirement: Configurable TalentLMS API key
The MCP server SHALL require the TalentLMS API key to be supplied via MCP settings rather than a hardcoded value, and it MUST use that key for all TalentLMS HTTP requests.

#### Scenario: API key provided in settings
- **WHEN** a client configures the MCP server with a TalentLMS API key in settings
- **THEN** all TalentLMS HTTP requests SHALL include that key in the `X-API-Key` header.

#### Scenario: Missing API key
- **WHEN** a TalentLMS MCP tool is invoked without a configured API key
- **THEN** the server SHALL return a clear error indicating the TalentLMS API key is required.

