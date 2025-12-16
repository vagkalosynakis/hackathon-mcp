# Change: Add Doctrine MySQL connection

## Why
We need a first-class Doctrine DB connection to the external `mysql` service so the MCP server can persist and fetch data using the provided sandbox credentials.

## What Changes
- Add Doctrine DBAL/ORM dependency and connection configuration targeting the `mysql` service on the external Docker network.
- Initialize the Doctrine connection with sensible defaults and clear failures when credentials are invalid or missing.
- Add a simple `list_courses` MCP tool that uses the Doctrine connection to fetch all `course` records from MySQL.
- Document required environment variables (host/user/password/db/port) using the provided sandbox values as defaults.

## Impact
- Affected specs: persistence
- Affected code: composer dependencies, server bootstrap/config, Docker/ENV wiring for MySQL access

