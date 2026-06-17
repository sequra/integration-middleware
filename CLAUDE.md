# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`sequra/middleware` is a **Laravel 12 package** (not a standalone app) that adapts SeQura's platform-agnostic `sequra/integration-core` library into a cloud-hosted, multi-tenant HTTP service. It is consumed as a Composer dependency by a host Laravel application that wires it in via `SeQuraMiddlewareServiceProvider`. PHP `^8.4` is required.

The core library (`vendor/sequra/integration-core`) owns all business logic, domain entities, the task-execution queue, and the `AdminAPI`/`ServiceRegister`/`RepositoryRegistry` infrastructure. This package supplies only the platform-specific implementations: Laravel HTTP routing/controllers, a Laravel-backed ORM/persistence layer, and the multi-tenant context model.

## Commands

```bash
composer install                       # install deps
vendor/bin/phpunit                     # run all tests
vendor/bin/phpunit tests/Unit/LoggerServiceTest.php   # single test file
vendor/bin/phpunit --filter testMethodName            # single test by name
```

**Test setup:** copy `tests/.env.example` to `tests/.env` and fill in DB params; the target MySQL database must already exist on the server (tests run against a real MySQL connection configured in `tests/config/database.php`, not an in-memory store). There is no committed `phpunit.xml`; tests bootstrap a minimal Laravel app via `tests/bootstrap/app.php` + the `CreatesApplication` trait. There is no linter configured in the repo.

## Architecture

### Bootstrapping (start here)
`src/BootstrapComponent.php` extends the core's `BootstrapComponent` and is the single wiring point. Two methods matter:
- `initServices()` registers platform implementations into the core `ServiceRegister` (e.g. `ShopLoggerAdapter` → `LoggerService`, `EncryptorInterface` → `Encryptor`, `VersionServiceInterface`, `DisconnectServiceInterface`, `TenantService`).
- `initRepositories()` maps every core domain entity (`SeQuraOrder`, `ConnectionData`, `PaymentMethod`, `AdvancedSettings`, …) to a middleware repository class in `RepositoryRegistry`. **When the core library adds a new persisted entity, it must be registered here** or persistence will fail.

The host app must call this bootstrap and register the package's service provider.

### Multi-tenancy (the central concept)
A "tenant" == a store context, identified by a `storeId` string. The model is **table-per-tenant for entity data, shared tables for global data**:

- `InitializeAdminContext` middleware reads `storeId` (or `m_storeId`) from each admin request and calls `ConfigurationManager::setContext($storeId)`. Everything downstream keys off this context.
- `TableNameProvider` (a `Singleton`) resolves table names by substituting `{id}` in a format string with `md5(context)`. So `EntityRepository` (table `tenant_{id}_entities`) reads/writes a **physically separate table per store**, created by `CreateEntityTable` migration via `Migrator::createTenantSpecificTables()`.
- **Global/shared tables** are NOT tenant-prefixed: `configurations` (`ConfigurationRepository`), `execution_queue_items` (`QueueItemRepository`), `processes` (`ProcessRepository`), `tenants` (`TenantRepository`). The committed Laravel migrations in `src/database/migrations/` create only these global tables.
- `TenantService` looks up / creates rows in the global `tenants` table keyed by `context`.
- Uninstall (`src/Uninstall/`): `UninstallService::uninstall()` drops the tenant-specific tables, removes the tenant row, and purges that tenant's rows from the global tables via `GlobalDataDeleter`. `deleteTenantFromGlobalTable()` is abstract — the host app implements it.

### ORM / persistence layer (`src/ORM/`)
Bridges the core's storage-agnostic `RepositoryInterface` + `QueryFilter` API onto Laravel's `DB` query builder.

- Entities are stored as a generic row: a `type` discriminator, up to 10 generic `index_N` columns, and a JSON `data` blob holding the serialized entity. **Only fields declared as indexes on the core entity are queryable**; `OrmEntityTransformer` throws `QueryFilterInvalidParamException` if a `QueryFilter` references a non-indexed column. `IndexHelper` (from core) maps entity fields → `index_N` columns.
- Repository hierarchy: `BaseRepository` (abstract; implements core `RepositoryInterface` + `ConditionallyDeletes`) → `ContextAwareRepository` / `TenantSpecificRepository`. `EntityRepository` is the catch-all for most core entities and resolves a tenant-prefixed table; `QueueItemRepository` and `ConfigurationRepository` use fixed global table names and override `getTransformer()` where needed (e.g. `QueueItemEntityTransformer`).
- `select`/`update`/`save` go through the transformer, which serializes to/from the JSON `data` column and writes index columns alongside.

### HTTP layer (`src/Http/`)
- Routes: `src/routes/sequra.php` defines a public `healthz` check, a public async-process endpoint (`sequra/async/asyncprocess/guid/{guid}` → drives the core task queue), and a `sequra/admin/*` group for the configuration UI backend.
- Controllers extend `BaseController` and are thin: they delegate to the core `AdminAPI::get()->...($storeId)` facade and `response()->json(...)` the result. Add business logic in core, not here.
- Admin middleware stack (applied to `sequra/admin/*`): `sequra.auth` (`AdminAPIValidator`) → `sequra.validate` (`ValidateAdminRequest`) → `Cors` → `InitializeAdminContext`.
- **`AdminAPIValidator` ships as a stub that always throws 401.** It is registered only if the host app hasn't already aliased `sequra.auth` (see `loadMiddlewareAlias()`); the host is expected to override `sequra.auth`/`sequra.validate` with real auth. Don't add real authentication logic to this package's stub.

## Conventions
- This package contains no domain/business logic. New behavior belongs in `integration-core`; this repo only adapts core to Laravel. Bumping the `sequra/integration-core` version is a recurring task (see git history) and may require registering new entities/services in `BootstrapComponent`.
- Persisted entity queries must use indexed fields only — adding a new queryable field means it must be declared as an index on the core entity.
- `LoggerService`, `Encryptor`, `TableNameProvider`, `Migrator` are `Singleton`s accessed via `getInstance()` / the core `ServiceRegister`, not instantiated directly.

## Working guidelines

Behavioral guidelines to reduce common mistakes. They bias toward caution over speed — for trivial tasks, use judgment.

### 1. Think before coding
**Don't assume. Don't hide confusion. Surface tradeoffs.** Before implementing:
- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them — don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask. (Here this often means: does this behavior belong in `integration-core` or in this adapter? Is a field actually indexed on the core entity before you query it?)

### 2. Simplicity first
**Minimum code that solves the problem. Nothing speculative.**
- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask: "Would a senior engineer say this is overcomplicated?" If yes, simplify. Controllers here are intentionally thin — keep them that way.

### 3. Surgical changes
**Touch only what you must. Clean up only your own mess.** When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it — don't delete it.
- Remove imports/variables/functions that YOUR changes made unused; don't remove pre-existing dead code unless asked.

The test: every changed line should trace directly to the request.

### 4. Goal-driven execution
**Define success criteria. Loop until verified.** Transform tasks into verifiable goals:
- "Add validation" → "Write tests for invalid inputs, then make them pass."
- "Fix the bug" → "Write a `tests/Unit` test that reproduces it, then make it pass" (run with `vendor/bin/phpunit --filter`).
- "Register a new core entity" → "Add it to `BootstrapComponent::initRepositories()`, then verify persistence via a repository test."
- "Refactor X" → "Ensure `vendor/bin/phpunit` passes before and after."

For multi-step tasks, state a brief plan with a verification check per step. Strong success criteria let you loop independently; weak criteria ("make it work") require constant clarification.

**These guidelines are working if:** fewer unnecessary changes in diffs, fewer rewrites due to overcomplication, and clarifying questions come before implementation rather than after mistakes.
