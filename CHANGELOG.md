# Changelog

## Unreleased
- Reworked weak request DTOs to use typed constructors instead of raw array input for active market, instrument, and order flows
- Aligned market quote and candle requests with documented Finam REST resource paths
- Refined transport integer accessors to normalize numeric strings into a single `int` contract
- Added API modules (connect/account/instrument/order/market) with DTO-driven request contracts and interfaces
- Added client factory and flexible token-based construction, including connect() helper
- Added shared request validation and new InvalidRequestException
- Simplified authentication handling and fallback token logic
- Removed env-based configuration; config now requires explicit values and docs updated

## v0.1.0
- Initial public release
- Laravel service provider
- Base REST client (Guzzle)
- Config file and publishing tag
- Testbench + PHPUnit + PHPStan setup
- GitHub Actions CI
