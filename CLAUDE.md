# Dashy Project Rules

These project-specific rules take precedence over the general Laravel/Boost guidance below. Read them before writing or editing any code in this repository.

## 1. Domain-Driven Architecture

Code is organized by business domain, not by Laravel artifact type.

- New domain code lives under `app/Domains/<DomainName>/` with subfolders as needed: `Models/`, `Services/`, `Actions/`, `Events/`, `Listeners/`, `DTOs/`, `Enums/`, `Exceptions/`, `Policies/`.
- A domain owns its data. Other domains may only call its **public services** — never reach into its actions, models, or internals.
- Cross-domain orchestration happens in a coordinating service (often in a shared/coordinator domain), not by reaching across boundaries.
- Cross-cutting concerns (logging, request lifecycle, auth scaffolding) stay outside `Domains/`. Exception: `App\Models\User` stays where Fortify expects it but is conceptually owned by the Auth domain.
- Domain folders use PascalCase, singular when describing an aggregate (e.g., `Workspace`, `Billing`, `Auth`).

## 2. UI → Service → Action Flow

Strict three-layer pattern. Skipping a layer is a bug.

> ⚠️ Naming note: in this project, **"Action class" = data-access layer**. This is opposite to the common Spatie/Laravel-Beyond-CRUD convention where Actions hold the business logic. Read the rules below carefully; do not import patterns from other projects without translating them.

**UI layer** — Livewire/Volt components, Folio pages, Blade views, Controllers, Form Requests.
- May call services. Must NOT touch the database directly: no `Model::query()`, no `DB::`, no Eloquent reads or writes from a controller, component, or page.
- Owns input parsing, request validation (shape), view rendering, and HTTP response shape.

**Service layer** — `app/Domains/<Domain>/Services/`.
- Holds all business logic and orchestration: business-rule validation, authorization decisions, fan-out to actions, transaction boundaries, dispatching events/jobs/notifications.
- Must NOT execute SQL, Eloquent persistence, or Eloquent query methods directly. Both reads and writes go through actions.
- Owns transaction scope when more than one action must succeed atomically.

**Action layer** — `app/Domains/<Domain>/Actions/`.
- The ONLY layer allowed to touch the database. Eloquent calls, raw queries, and any `DB::` usage live here.
- Each action is a single, focused DB operation (one create / one update / one delete / one targeted read). Actions do not call other actions and do not contain business rules.
- Actions return models, DTOs, or scalars.

**What goes where — examples:**
- *Register a user*: Form Request validates shape → `RegisterUser` service handles uniqueness, terms, policy → calls `CreateUserAction` → dispatches `UserRegistered` event.
- *Show a workspace*: Livewire component calls `WorkspaceService::find($id)` → service authorizes → calls `FindWorkspaceAction` → returns the model.

**Notes & exceptions:**
- Eloquent model *definitions* (relationships, casts, scopes, accessors, mutators) live on the model class itself — that's modeling, not data access. The rule applies to *invoking* persistence/query methods.
- Policies are called from services. UI may also call `Gate::authorize()` for short-circuit checks, but the canonical authorization happens in the service.
- The shipped Livewire starter kit is grandfathered. This includes Fortify scaffolding (`app/Actions/Fortify/`, `app/Concerns/`), `app/Livewire/Actions/Logout.php`, the settings Volt components (`resources/views/pages/settings/⚡*.blade.php` and their layouts/partials), and any auth pages we have not yet redesigned (`forgot-password`, `reset-password`, `verify-email`, `confirm-password`, `two-factor-challenge`). New domain code we author follows all rules above. When we redesign or substantially modify a kit page, it must come into compliance at that point — it's no longer "kit code".

## 3. Reuse Before Writing

Before writing any new method, class, helper, component, or CSS class:

1. Search the codebase for existing implementations: grep for likely method names, related domain terms, similar inputs/outputs, and similar UI patterns (`dashy-*`, Flux components).
2. If something close exists, **reuse > compose > extend > duplicate**. Duplicate only when the cases will genuinely diverge.
3. Apply the rule of three: two occurrences is a coincidence, three is the threshold to extract a shared abstraction. Don't pre-abstract on the first duplicate.
4. When extending shared code, prefer additive changes (new optional parameter, new method) over breaking the existing call sites.

## 4. Color System

The UI uses **only** the brand palette plus its derived neutrals and a small set of semantic state colors. No ad-hoc hex values in components.

**Brand palette:**

| Token         | Hex       | Role                                                            |
|---------------|-----------|-----------------------------------------------------------------|
| Danube        | `#5992c6` | Primary action — buttons, links, focus rings, accents           |
| Torea Bay     | `#0a2a92` | Deep brand — pressed/hover states, dark emphasis                |
| Cocoa Brown   | `#31241f` | Dark accent — avatar pills, active dark tabs, sparing dark emphasis on cream |
| Shilo         | `#e9b8c9` | Soft accent — highlights, decorative moments                    |

**Surface scale** (light theme — cream page, white surfaces):
- `--bg` (`#f5f3ef`) — page background (warm cream)
- `--bg-deep` (`#ffffff`) — nested inputs, sidebar surface
- `--surface` (`#ffffff`) — cards, panels, popovers, modals
- `--surface-2` (`#f0eee9`) — hover state, section bg

**Derived neutrals** (charcoal scale, sized to read on cream):
- `--ink` (`#1a1a1a`) — primary text
- `--ink-muted` (`#6b6b6b`) — secondary text
- `--ink-dim` (`#9a9a9a`) — tertiary text, dividers, placeholders

**Semantic state colors** (use sparingly, only when the brand palette can't carry meaning):
- `--state-success`, `--state-error`, `--state-warning` — defined as named tokens, not raw hex.

**Implementation rules:**
- All colors live as CSS variables in `resources/css/app.css` and as Tailwind theme tokens.
- Components reference tokens via `var(--token)` or theme-mapped Tailwind classes — never raw hex values inline.
- When a new color need appears, propose a derived token (e.g., `--blue-soft` from Danube). If a need genuinely can't be expressed in the palette, flag it and discuss before introducing new brand colors.

## 5. Test Coverage

Every feature ships with tests. Not optional, not deferred.

**What "extensive" means:**
- **Happy path** — feature works under expected inputs.
- **Failure paths** — invalid input, unauthorized access, validation errors, conflict cases.
- **Edge cases** — empty/null/boundary values, idempotency where it applies.

**Where tests live, by layer:**
- **UI** → Feature tests in `tests/Feature/`. Cover the request/response cycle, validation errors, redirects, and view assertions.
- **Service** → Unit tests in `tests/Unit/Domains/<Domain>/Services/`. Cover business rules, authorization, and transaction behavior. Mock the action layer where useful.
- **Action** → Unit tests in `tests/Unit/Domains/<Domain>/Actions/`. Hit the database. Assert state changes.

**Bug fixes** include a regression test that reproduces the bug *before* the fix lands. Refactors require all existing tests to still pass; no test is removed without an explicit reason in the commit message.

A feature is "done" only when all applicable layers have tests, the full suite is green, and a `migrate:fresh` + run passes.

## 6. Find the Problem, Don't Just Make Tests Pass

When something fails, understand WHY before changing anything.

- **Read the failure** — run the test in isolation, look at actual vs expected, read the assertion message and stack trace.
- **Diagnose before patching.** Is the production code wrong, the test wrong, or the test data wrong? Each demands a different fix.
- **Never change a test just to make it green** unless you can explain that the test was asserting the wrong behavior. If you do change it, the commit message says why.
- **No silencing.** Don't wrap code in `try/catch` to swallow exceptions, don't add conditionals that paper over the symptom, don't comment out failing assertions, don't loosen an assertion to match buggy output. These hide real bugs.
- **Reproduce user-reported bugs as a test before fixing them.** That makes the fix verifiable and prevents silent regressions.
- **If you can't find the root cause** within reasonable effort, escalate to the user rather than ship a workaround.

Same discipline applies to runtime errors, browser console errors, and unexpected UI behavior — investigate the root cause, don't suppress the symptom.

## 7. Wrap All Database Interactions in Transactions

Every database operation must run inside a transaction so partial writes can never leak when something fails mid-operation.

- Use `DB::transaction(...)` (or the service-layer transaction boundary established in rule 2) — do not hand-roll `BEGIN`/`COMMIT`.
- Single-statement writes still go inside a transaction. Cheap insurance, consistent pattern.
- Multi-step writes that must succeed or fail together always share one transaction; never split them across sibling action calls without a wrapping transaction in the service.
- Wrap reads only when you need a consistent snapshot across multiple queries.
- Transactions are opened in the **service layer**, never in actions or UI. Actions stay single-purpose (rule 2).
- Never swallow a transaction failure to keep the request "succeeding" — let it bubble (rule 6).
- Same applies to queue jobs, console commands, and listeners that touch the database.

## 8. Do Only What I Ask — Nothing More

Stay strictly within the scope of the request.

- Do not add features, refactor untouched code, rename things, introduce abstractions, or "clean up" nearby files.
- Do not fix unrelated issues you happen to notice while working. Note them at the end of your reply and wait for explicit approval before acting.
- Do not expand a fix into a redesign. A bug fix changes the buggy lines, not the surrounding architecture.
- When the request is ambiguous, **ask** — do not implement both interpretations to be safe.
- Suggestions are welcome; unsolicited changes are not. The line is: words in the reply = fine, edits in the diff = not fine, until approved.

## 9. Place Every File, Directory, and Piece of Code in the Right Location

The project structure must stay organised and predictable as it grows. Before creating anything new:

1. Check the existing structure and respect what's already there — rule 1's domain layout (`app/Domains/<Domain>/...`), rule 2's UI/Service/Action split, sibling files for naming/shape, and the Boost guideline "stick to existing directory structure".
2. **New code goes next to similar code.** A new service belongs in `app/Domains/<Domain>/Services/`, a new action in `Actions/`, a new Livewire component where the existing ones live, etc.
3. **New directories are a last resort.** Only create one when no existing location reasonably fits, and never create new top-level base folders without approval.
4. **Never drop files at the project root, in scratch locations, or in arbitrary folders for convenience.** No `temp/`, `misc/`, `helpers/` catch-alls.
5. Tests mirror the structure of the code they test (rule 5): feature tests under `tests/Feature/`, unit tests under `tests/Unit/Domains/<Domain>/{Services,Actions}/`.
6. If you are unsure where something belongs, **ask before creating it** rather than guessing and reorganising later.

## 10. Every UI Element Must Be Responsive Across All Screen Sizes

Every component, page, and layout we build has to look and work well on **mobile, tablet, desktop, and wide screens**. Responsive is not a polish step at the end — it's a baseline requirement, designed in from the first markup pass.

**Target breakpoints (Tailwind defaults):**

| Tier | Width | Tailwind prefix | Representative device |
|------|-------|-----------------|-----------------------|
| Mobile | `< 640px` | (no prefix) | iPhone SE / 13 portrait |
| Tablet | `≥ 768px` | `md:` | iPad portrait |
| Desktop | `≥ 1024px` | `lg:` | 13–15" laptop |
| Wide | `≥ 1280px` / `≥ 1536px` | `xl:` / `2xl:` | external monitor / 4K |

**What "looks good" means at every tier:**
- **No horizontal scrolling** on any tier (except inside intentional overflow containers like wide tables or code blocks, where horizontal scroll is the design).
- **Tap targets ≥ 44×44 px** on mobile/tablet. Don't shrink buttons/links to dense desktop sizes on touch.
- **Text remains readable** — no fixed pixel widths that force line breaks at unreadable widths; respect line-length around 50–80 chars on desktop.
- **No content clipped or cut off** by overflow, fixed heights, or `whitespace-nowrap` on text.
- **Layouts reflow, not zoom.** Multi-column desktop layouts collapse to single-column on mobile (`grid-cols-1 md:grid-cols-2 lg:grid-cols-3`); never just shrink a desktop layout uniformly.
- **Images and embeds scale** — `max-w-full h-auto`, never fixed-pixel `width=`/`height=` on inline media.
- **Hero/sidebar/topbar layouts adapt** — sidebars become drawers or stack above content on mobile; sticky topbars stay sane on small screens.

**Implementation rules:**
- **Mobile-first by default.** Write base styles for mobile, then add `md:`/`lg:`/`xl:` modifiers that scale up. Never assume desktop and patch downward.
- **Use Tailwind's responsive prefixes**, not custom media queries, unless there's a specific reason the breakpoint system can't express. If a one-off breakpoint is genuinely needed, add it to the Tailwind theme so it's reusable.
- **Use Flux UI's responsive props** where available (`<flux:button class="w-full md:w-auto">`, etc.) before reaching for raw utility classes.
- **No fixed pixel widths** on layout containers. Prefer `max-w-*` constraints, fluid widths (`w-full`, `flex-1`), and the grid system.
- **Test before declaring done.** Every UI change must be checked at *all four* tiers — mobile (375px), tablet (768px), desktop (1280px), wide (≥1536px). Do this in the browser via DevTools' device toolbar; don't ship UI work without that pass.
- Existing kit pages that are not yet redesigned (rule 2's grandfathered list) are exempt until they are touched. The moment we substantially modify a kit page, this rule applies to it too.

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/socialite (SOCIALITE) - v5
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
