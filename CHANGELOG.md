# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-XX

### Added

- Initial release
- Base `ApiController` class with standardized request handling
- Automatic data collection from GET, POST, JSON, files, and route params
- Unified error response handling
- Exception to HTTP status mapping
- Helper methods for common responses:
  - `respondCreated()` - 201 Created
  - `respondNoContent()` - 204 No Content
  - `respondNotFound()` - 404 Not Found
  - `respondUnauthorized()` - 401 Unauthorized
  - `respondValidationError()` - 422 Unprocessable Entity
- File upload support via `getFileInput()`
- Complete PHPDoc documentation
- Unit test suite
- GitHub Actions CI/CD
- PSR-12 compliance
- PHPStan level 5 support

---

[1.0.0]: https://github.com/dcardenasl/ci4-api-base/releases/tag/v1.0.0
