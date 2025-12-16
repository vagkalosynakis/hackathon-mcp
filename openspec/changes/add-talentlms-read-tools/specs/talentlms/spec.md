## ADDED Requirements
### Requirement: TalentLMS authentication
The system SHALL require a TalentLMS base URL and API key provided via environment variables (e.g., `TLMS_BASE_URL`, `TLMS_API_KEY`) and use them for all outgoing read-only requests.

#### Scenario: Auth configured
- **WHEN** the server starts with both base URL and API key provided
- **THEN** all MCP tools use these credentials to call TalentLMS successfully

#### Scenario: Missing credentials
- **WHEN** either the base URL or API key is missing or empty
- **THEN** the server surfaces a clear, actionable error before serving requests

### Requirement: Read-only enforcement
The system SHALL only perform read operations against TalentLMS and MUST reject or short-circuit any attempted write/mutation paths.

#### Scenario: Attempted write is blocked
- **WHEN** a tool invocation would perform a write or mutation
- **THEN** the server returns an error stating that write operations are not allowed

### Requirement: Get users tool
The system SHALL expose an MCP tool `get_users` that returns users with metadata (e.g., last login, branch/department) to support audit and engagement scenarios.

#### Scenario: Users retrieved successfully
- **WHEN** `get_users` is invoked and TalentLMS responds
- **THEN** the server returns the list of users with key metadata needed for audit/risk reporting

#### Scenario: Users unavailable
- **WHEN** `get_users` is invoked but the TalentLMS API is unreachable or errors
- **THEN** the server returns an error describing the connectivity or API issue

### Requirement: Get courses tool
The system SHALL expose an MCP tool `get_courses` that returns course metadata needed for branch performance and learning-path planning.

#### Scenario: Courses retrieved successfully
- **WHEN** `get_courses` is invoked and TalentLMS responds
- **THEN** the server returns course metadata (titles, durations, identifiers) suitable for reporting

#### Scenario: Courses unavailable
- **WHEN** `get_courses` is invoked but the TalentLMS API is unreachable or errors
- **THEN** the server returns an error describing the issue

### Requirement: Get certification tool
The system SHALL expose an MCP tool `get_certification` that returns certification records with expiry information and recertification status.

#### Scenario: Certifications retrieved successfully
- **WHEN** `get_certification` is invoked and TalentLMS responds
- **THEN** the server returns certification data including expiry windows and user recertification status

#### Scenario: Certifications unavailable
- **WHEN** `get_certification` is invoked but the API is unreachable or errors
- **THEN** the server returns an error describing the issue

### Requirement: Get learner progress tool
The system SHALL expose an MCP tool `get_learner_progress` that returns learner progress and risk signals for a given course or set of learners.

#### Scenario: Progress retrieved successfully
- **WHEN** `get_learner_progress` is invoked and TalentLMS responds
- **THEN** the server returns progress data (completion %, last activity, blocking units) to identify at-risk learners

#### Scenario: Progress unavailable
- **WHEN** `get_learner_progress` is invoked but the API is unreachable or errors
- **THEN** the server returns an error describing the issue

### Requirement: Get learning path tool
The system SHALL expose an MCP tool `get_learning_path` that returns learning path structure and assigned courses.

#### Scenario: Learning path retrieved successfully
- **WHEN** `get_learning_path` is invoked and TalentLMS responds
- **THEN** the server returns the learning path structure and course sequence for planning

#### Scenario: Learning path unavailable
- **WHEN** `get_learning_path` is invoked but the API is unreachable or errors
- **THEN** the server returns an error describing the issue

### Requirement: Get skill content tool
The system SHALL expose an MCP tool `get_skill_content` that returns skills with recommended courses and assessments.

#### Scenario: Skill content retrieved successfully
- **WHEN** `get_skill_content` is invoked and TalentLMS responds
- **THEN** the server returns skill definitions with recommended courses and assessments

#### Scenario: Skill content unavailable
- **WHEN** `get_skill_content` is invoked but the API is unreachable or errors
- **THEN** the server returns an error describing the issue

