# Code Quality Standards

This document outlines the code quality standards and practices for PerfAudit Pro.

## Type Safety

- All methods MUST have type hints for parameters
- All methods MUST have return type declarations
- Use strict type checking with `declare(strict_types=1)` where appropriate
- Use PHPDoc `@param` and `@return` annotations with detailed type information

## Input Validation

- All user input MUST be validated at boundaries using `Validator` class
- All database inputs MUST use prepared statements
- All output MUST be escaped using WordPress escaping functions
- Never trust user input - validate, sanitize, escape

## Error Handling

- Use `Logger` class instead of `error_log()` directly
- Return `WP_Error` objects for recoverable errors
- Throw exceptions only for programming errors
- Log all errors with appropriate context

## Security

- All REST API endpoints MUST have permission callbacks
- All AJAX handlers MUST verify nonces
- All database queries MUST use prepared statements
- All output MUST be escaped
- Never expose sensitive data in error messages

## Performance

- Limit query results to prevent memory issues
- Use caching where appropriate
- Optimize database queries
- Avoid N+1 query problems
- Use lazy loading for heavy operations

## Memory Safety

- Limit array sizes (e.g., max 1000 items for bulk operations)
- Use generators for large datasets
- Clear large variables after use
- Monitor memory usage in loops

## Documentation

- All classes MUST have class-level PHPDoc
- All public methods MUST have method-level PHPDoc
- Complex logic MUST have inline comments
- Use `@since` tags for version tracking

## Testing

- Pure functions should be easily testable
- Mock external dependencies
- Test edge cases and error conditions
- Maintain high test coverage for critical paths

