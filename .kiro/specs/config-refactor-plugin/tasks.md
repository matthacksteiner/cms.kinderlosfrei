# Implementation Plan

- [x] 1. Create plugin structure and entry point

  - Create directory structure for `site/plugins/baukasten-api/`
  - Implement plugin entry point with proper Kirby registration
  - Set up autoloading for plugin classes
  - _Requirements: 2.1, 2.2, 4.4_

- [x] 2. Extract and organize helper functions into classes

  - [x] 2.1 Create NavigationHelper class with navigation-related functions

    - Move `getPageNavigation`, `getNavigationSiblings`, `getEffectiveParent` functions
    - Implement proper class structure with static methods
    - _Requirements: 2.3, 4.3_

  - [x] 2.2 Create UrlHelper class with URL generation functions

    - Move `generatePageUri`, `getSectionToggleState` functions
    - Organize flat URL resolution logic into cohesive methods
    - _Requirements: 2.3, 4.3_

  - [x] 2.3 Create LanguageHelper class with language functions

    - Move `getTranslations`, `getAllLanguages`, `getDefaultLanguage` functions
    - Ensure proper handling of multi-language scenarios
    - _Requirements: 2.3, 4.3_

  - [x] 2.4 Create SiteDataHelper class with site data extraction functions

    - Move `getFavicon`, `getNavigation`, `getLogoFile`, `getLogoCta`, `getFonts`, `getFontSizes`, `getHeadlines`, `getAnalytics` functions
    - Group related data extraction functions logically
    - _Requirements: 2.3, 4.3_

- [x] 3. Create FlatUrlResolver service

  - Extract complex flat URL resolution logic into dedicated service
  - Move `handleFlatUrlResolution`, `findPagesByFlatUrl`, `resolvePriorityConflict`, `tryHierarchicalFallback`, `isSectionPageAccessible` functions
  - Ensure all URL routing edge cases are handled correctly
  - _Requirements: 4.3, 5.2_

- [x] 4. Create API endpoint classes

  - [x] 4.1 Implement IndexApi class for index.json endpoint

    - Extract `indexJsonData` and `indexJson` functions into class
    - Maintain identical JSON response structure
    - _Requirements: 2.3, 5.3_

  - [x] 4.2 Implement GlobalApi class for global.json endpoint

    - Extract `globalJsonData` and `globalJson` functions into class
    - Preserve all current global data fields
    - _Requirements: 2.3, 5.3_

- [x] 5. Create Routes class for route management

  - Extract all route definitions from config.php
  - Implement static method to return route array
  - Maintain all current routing patterns and behaviors including individual page JSON endpoints
  - _Requirements: 4.1, 5.1, 5.2_

- [x] 6. Create comprehensive test suite

  - [x] 6.1 Write unit tests for helper functions

    - Test NavigationHelper methods with mock page objects
    - Test UrlHelper functions with various URL scenarios
    - Test LanguageHelper with different language configurations
    - Test SiteDataHelper functions with mock site data
    - _Requirements: 3.3_

  - [x] 6.2 Write integration tests for API endpoints

    - Test index.json endpoint returns expected data structure
    - Test global.json endpoint includes all required fields
    - Test individual page JSON endpoints work correctly
    - _Requirements: 3.1_

  - [x] 6.3 Write routing tests for URL resolution

    - Test flat URL resolution with section toggle enabled/disabled
    - Test hierarchical URL fallback scenarios
    - Test edge cases and conflict resolution
    - _Requirements: 3.2_

- [x] 7. Refactor config.php to use plugin

  - Remove all helper functions from config.php
  - Update routes configuration to use plugin Routes class
  - Remove unused imports (Dir, F)
  - Maintain all existing configuration options
  - _Requirements: 1.1, 1.2, 5.4_

- [x] 8. Run validation tests and ensure backward compatibility

  - Execute complete test suite to verify functionality
  - Compare API responses before and after refactoring
  - Test all URL routing scenarios work identically
  - _Requirements: 3.1, 5.1, 5.2, 5.3_
