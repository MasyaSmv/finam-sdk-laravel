# Changelog

## Unreleased
- Added `AuthService` token issuance flow via `Finam::issueToken($secret)` with typed DTO and facade support
- Added paginated `AllAssets` session flow with typed page DTO and cursor contract
- Added typed asset exchange, clock, and schedule session flows with dedicated DTO and collection contracts
- Added typed market depth and latest trades session flows with dedicated DTO and collection contracts
- Unified SDK exception diagnostics through a shared API diagnostic context object
- Refined asset DTO and mapping to match the documented Finam asset model more closely
- Removed undocumented `ReplaceOrder` contract until Finam REST docs provide a confirmed replacement/update endpoint
- Switched order cancellation to the documented REST DELETE endpoint and removed the fake cancel payload contract
- Moved GetAsset symbol to the resource path and kept only optional account context in the query contract
- Added typed SDK config objects so array config now stays only on the Laravel config boundary
- Aligned instrument asset params and options chain requests with documented Finam REST resource paths
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
