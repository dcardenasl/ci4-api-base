# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CI4 API Base Controller is a lightweight CodeIgniter 4 library that provides a standardized base controller for building RESTful APIs. The library handles automatic request data collection from multiple sources (GET, POST, JSON, files) and provides consistent JSON responses with proper HTTP status codes.

**Key Principle**: The controller delegates business logic to service classes, maintaining a clean separation between request handling and business logic.

## Commands

### Testing
```bash
# Run all tests
composer test
# Or directly
vendor/bin/phpunit

# Run tests with coverage
vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml

# Run specific test
vendor/bin/phpunit tests/Unit/ApiControllerTest.php
```

### Code Quality
```bash
# Run static analysis (PHPStan level 5)
composer analyse
# Or directly
vendor/bin/phpstan analyse src --level=5
```

### Dependency Management
```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

## Architecture

### Core Components

**ApiController** (`src/Controllers/ApiController.php`)
- Abstract base controller that all API controllers should extend
- Uses CodeIgniter's `ResponseTrait` for JSON responses
- Provides the `handleRequest()` method as the main entry point for API operations

### Request Flow

1. **Data Collection**: `collectRequestData()` merges data from:
   - GET parameters (`$request->getGet()`)
   - POST data (`$request->getPost()`)
   - Raw input (`$request->getRawInput()`)
   - Uploaded files (`$request->getFiles()`)
   - JSON body (parsed via `getJsonData()`)
   - Route parameters (passed as `$item` array)

2. **Service Delegation**: Controller calls the service method with merged data
   - Services must return arrays
   - Success response: `['data' => ...]`
   - Error response: `['errors' => [...]]`

3. **Status Determination**: `determineStatus()` selects HTTP status code
   - Presence of `errors` key → 400 Bad Request
   - Otherwise → Status from `getSuccessStatus()` (controller-defined)

4. **Response**: Returns JSON with appropriate status code

### Exception Handling

The `handleException()` method maps exceptions to HTTP status codes:
- `InvalidArgumentException` → 400 Bad Request
- `RuntimeException` → 500 Internal Server Error
- Other exceptions → 400 Bad Request (default)

### Testing Strategy

Tests use anonymous classes to:
- Create concrete implementations of the abstract `ApiController`
- Expose protected methods for testing (e.g., `publicCollectRequestData()`)
- Mock the service layer using anonymous service classes

**Important**: Use reflection to inject protected properties (`$request`, `$response`) into the controller during tests. See `injectProperty()` method in `ApiControllerTest.php`.

### Namespace Convention

- Package namespace: `dcardenasl\CI4ApiBase`
- Test namespace: `Tests`
- Controllers: `dcardenasl\CI4ApiBase\Controllers`

## Implementation Pattern

When extending `ApiController`, you must implement:

1. **`getService()`**: Return the service instance that contains business logic
2. **`getSuccessStatus(string $method)`**: Return HTTP status code for successful operations
   - Common pattern: Use `match` expression to map service methods to status codes
   - Example: `'store' => 201`, `'destroy' => 204`, `default => 200`

Controllers should be thin - they only:
- Define routes/endpoints
- Call `handleRequest()` with the service method name
- Pass route parameters as the second argument to `handleRequest()`

## Key Behaviors

- JSON parsing errors are silently handled (returns empty array)
- Empty request bodies return empty array
- Services receive a single merged array with all request data
- File uploads use `getFileInput($fieldName)` helper method
- All responses are JSON formatted
- The library follows PSR-12 coding standards
