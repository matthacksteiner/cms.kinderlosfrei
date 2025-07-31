# Requirements Document

## Introduction

This feature involves refactoring the existing `site/config/config.php` file to create a more maintainable and elegant solution. The current configuration file contains a large amount of helper functions mixed with configuration logic, making it difficult to maintain and test. The goal is to extract these helper functions into a dedicated Kirby plugin called `baukasten-api` while maintaining all existing functionality and ensuring backward compatibility.

## Requirements

### Requirement 1

**User Story:** As a developer, I want the configuration file to be clean and focused only on configuration logic, so that it's easier to read and maintain.

#### Acceptance Criteria

1. WHEN the config file is viewed THEN it SHALL contain only configuration arrays and minimal logic
2. WHEN helper functions are needed THEN they SHALL be loaded from the plugin automatically
3. WHEN the refactored code runs THEN it SHALL produce identical output to the current implementation

### Requirement 2

**User Story:** As a developer, I want helper functions organized in a dedicated plugin, so that they can be properly tested and reused across the application.

#### Acceptance Criteria

1. WHEN the plugin is created THEN it SHALL be located at `site/plugins/baukasten-api/`
2. WHEN the plugin loads THEN it SHALL make all helper functions available globally
3. WHEN functions are called THEN they SHALL behave identically to the current implementation
4. WHEN the plugin structure is examined THEN it SHALL follow Kirby plugin conventions

### Requirement 3

**User Story:** As a developer, I want comprehensive tests for the API functionality, so that I can ensure the refactoring doesn't break existing behavior.

#### Acceptance Criteria

1. WHEN tests are run THEN they SHALL verify that all JSON endpoints return identical data
2. WHEN URL routing is tested THEN it SHALL handle all current routing scenarios correctly
3. WHEN helper functions are tested THEN they SHALL produce expected outputs for various inputs
4. WHEN the test suite runs THEN it SHALL pass completely before and after refactoring

### Requirement 4

**User Story:** As a developer, I want the plugin to be well-organized with clear separation of concerns, so that future maintenance is straightforward.

#### Acceptance Criteria

1. WHEN the plugin structure is examined THEN it SHALL separate routing logic from helper functions
2. WHEN API endpoints are reviewed THEN they SHALL be organized in dedicated classes or files
3. WHEN helper functions are grouped THEN they SHALL be logically organized by functionality
4. WHEN the plugin is loaded THEN it SHALL register all routes and functions automatically

### Requirement 5

**User Story:** As a developer, I want the refactoring to maintain full backward compatibility, so that existing functionality continues to work without changes.

#### Acceptance Criteria

1. WHEN the refactored system runs THEN all existing API endpoints SHALL continue to work
2. WHEN URL routing is tested THEN all current URL patterns SHALL resolve correctly
3. WHEN JSON responses are compared THEN they SHALL be identical before and after refactoring
4. WHEN the system is deployed THEN no breaking changes SHALL be introduced
