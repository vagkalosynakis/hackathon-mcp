## ADDED Requirements
### Requirement: Doctrine MySQL connection
The system SHALL provide a Doctrine DB connection to the external `mysql` service on the shared Docker network using MySQL driver settings sourced from environment variables (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`) with defaults of host `mysql`, port `3306`, database `local_sandbox`, user `usr_local_sandbox176580221027`, and password `5GyGpkvv@l`.

#### Scenario: Successful connection on startup
- **WHEN** all required env vars are provided (or defaults are used)
- **THEN** the MCP server initializes a Doctrine connection to MySQL during bootstrap and surfaces a clear success or usable connection handle

#### Scenario: Missing or invalid credentials
- **WHEN** any required credential is missing or invalid
- **THEN** the server startup fails fast with an actionable error that identifies the missing/invalid field without falling back to unsafe values

#### Scenario: External network reachability
- **WHEN** the container runs on the configured external Docker network
- **THEN** the connection uses host `mysql` and port `3306` to reach the MySQL service and reports connectivity failures clearly if the service is unreachable

