# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

# [v4.0.0](https://github.com/sequra/integration-middleware/tree/v4.0.0)
Breaking release. Host applications must raise their PHP and Laravel versions and update their onboarding
frontend before upgrading.

### Removed
- `EloquentTransformer` and `ContextAwareTransformer`. Both were unused inside the package but were part of its
  public API, so host code extending or type hinting them will no longer load.
- The `sequra/admin/general-settings/payment-methods` route, its `sequra.admin.general-settings.payment-methods`
  name and `GeneralSettingsController::getShopPaymentMethods()`. The core dropped `getShopPaymentMethods` along
  with the replacement payment method feature, so the endpoint had only ever answered an unknown error. Hosts
  building a URL from the route name must drop the reference.
- The `replacementPaymentMethod` field of `POST sequra/admin/general-settings`.

### Changed
- Requires PHP `^8.5` (was `^8.4`), `laravel/framework` `^13.23.0` (was `^12.36.0`) and
  `sequra/integration-core` `^5.8` (was `~5.3.0`).
- `POST sequra/admin/connection` carries one credential set per deployment: `connectionData` is a list of
  `{merchantId, username, password, deployment}` next to `environment` and `sendStatisticalData`. The previous
  flat payload is no longer accepted.
- `POST sequra/admin/connection/validate` requires a `deployment`.
- `POST sequra/admin/disconnect` requires `deploymentId` and `isFullDisconnect`.
- Tables behind `ContextAwareRepository` must carry a `context` column: every read filters on it and every
  write populates it.

### Added
- `AffiliateSettings`, `BannerSettings` and `ExpressCheckoutSettings` are registered for persistence.

### Fixed
- Settings and onboarding errors answer with a valid HTTP status. The translatable error code was being passed
  as the status, and the core reports unhandled errors as status 0, so every error surfaced as a 500.
- `sequra/admin/disconnect` and `sequra/admin/payment-methods` pass the request objects the core has required
  since 5.3. Both previously answered 200 with an unknown-error body while doing nothing.
- General settings no longer store the removed replacement payment method as the default services end date.
- Context-aware entities are written with the `type` every read filters on, so their rows can be found again.

# [v1.0.1](https://github.com/sequra/integration-middleware/tree/v1.0.1)
- Updated to all the dev changes and prepared a 1.0.1 release
