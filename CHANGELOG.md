# Changelog

All notable changes to `orthanc-client` will be documented in this file.

## 1.0.0 - 2026-02-13

### Added
- Initial release
- HTTP client for Orthanc server
- Automatic exception reporting
- Context builder (auto-includes app, user, IP, route)
- Retry mechanism with exponential backoff
- Queue support for async notifications
- Fallback logging when server is unreachable
- Commands: `orthanc:test-connection`, `orthanc:status`
- Multiple notification levels (critical, error, warning, info, success, debug)
- Configuration for ignoring specific exceptions
- Comprehensive documentation
