# FreedomStack — Development log

A running note of what’s been built and changed. Update this file as work is completed.

---

## Dashboard (post–login/registration)

**References:** `FREEDOMSTACK_DASHBOARD_INSTRUCTIONS.md`, `freedomstack-dashboard-v2.html`, `CURSOR.md`.

### Backend

- **Migrations**
  - `2025_02_22_100000_add_last_login_to_users_table.php` — added `last_login_at` (nullable timestamp) to `users`.
  - `2025_02_22_100001_add_has_no_debts_investments_to_financial_profiles_table.php` — added `has_no_debts` and `has_no_investments` (boolean, default false) to `financial_profiles`.
- **Auth**
  - `app/Http/Controllers/Api/AuthController.php` — on successful login, sets `last_login_at` to `now()`.
  - `app/Models/User.php` — `last_login_at` in `fillable` and `casts`.
- **Financial profile**
  - `app/Models/FinancialProfile.php` — `has_no_debts` and `has_no_investments` in `fillable` and `$casts`.
- **Dashboard API**
  - `app/Http/Controllers/Api/DashboardController.php` — single `__invoke` for `GET /dashboard` returning:
    - **welcome** — title/subtitle (new vs returning using `last_login_at` / `created_at`).
    - **profile_completeness** — 5 steps (expenses, freedom number, income, debts, investments), `pct`, `complete`; each step has `done`, `route`, `time` where applicable.
    - **stats** — freedom_number, net_worth, freedom_progress, time_to_freedom; each with value, delta vs previous snapshot, `needs_data`, `cta_route` for empty states.
    - **checkin** — state (`getting_started` | `first` | `nudge` | `ok`), title, subtitle, button_text, button_route, next_checkin_date, last_snapshot_date.
    - **expenses_summary** — `total_cents`, `by_category` (category + amount_cents).
    - **debts** — list with id, name, balance_cents, original_balance_cents, apr, payoff_pct (for bar width).
    - **has_no_debts_flag** — from profile.
    - **recommendations_card** — state (`locked` | `loading` | `empty` | `populated`), title, subtitle, items (top 3 with id, title, impact).
    - **journey** — list of `{ snapshot_date, net_worth_cents }` for chart.
    - **badges** — `action_plan_count`, `progress_needs_update`, `profile_incomplete`.
  - `routes/api.php` — `Route::get('/dashboard', DashboardController::class)` under `auth:sanctum`.

### Frontend

- **Dashboard page** — `resources/ts/pages/dashboard/index.vue`
  - Fetches `GET /dashboard`; renders welcome, profile completeness (progress bar + 5 step cards), four stat cards (Freedom Number, Net Worth, Freedom Progress, Time to Freedom), check-in card (nudge vs ok; “Got it” dismisses for session), and 2×2 grid: Your journey, Monthly expenses, Top recommendations, Debt snapshot.
  - Handles `needs_data` and empty states; debt bars colored by APR (high/med/low); recommendations locked/loading/list.
  - Styling aligned with prototype (e.g. `--fs-green`, `--fs-amber`, `--fs-red`, stat-card, profile-completion, checkin-card, content-card).
- **Dashboard badges**
  - `resources/ts/@core/stores/dashboardBadges.ts` — Pinia store with `badges` and `setBadges()`; `useNavItemsWithBadges(staticNavItems)` merges badges into nav (Action Plan count, Progress “Update”, My Finances dot).
  - Dashboard page calls `useDashboardBadgesStore().setBadges(res.data.badges)` after successful fetch.
  - `resources/ts/layouts/components/DefaultLayoutWithVerticalNav.vue` — uses `useNavItemsWithBadges(navItemsBase)` so the vertical nav shows reactive badges.

### Optional / not yet done

- **Journey chart** — Replace placeholder with ApexCharts area chart (net worth over time + freedom number line).
- **AI trigger** — When profile completeness hits 100% for the first time, trigger `GenerateRecommendations` job (e.g. from DashboardController or a listener).
- **Migrations** — Run `php artisan migrate` where DB is available so new columns exist.

---

## Model and UI fixes

- **FinancialProfile.php** — Resolved “Cannot redeclare …::casts()” by replacing the `casts()` method with the `$casts` array property. Single source of attribute casting; no duplicate method.
- **Landing page comparison table** — `resources/ts/views/front-pages/landing-page/ComparisonSection.vue`: removed Vuetify `VTable` default background so the section’s dark background (`--fs-black`) shows through; set `background: transparent` on the table and its `thead`, `tbody`, `tr`, `th`, `td`.

---

## Conventions (from CURSOR.md / .cursorrules)

- Financial math in PHP with bcmath; monetary values in DB as integers (cents).
- No PII to OpenRouter; AI jobs via Laravel Queue.
- Vuexy/Vuetify design system; scan `vuexy-vue-laravel-template/` and `figma/` before adding net-new UI.

---

*Last updated: 2025-02-21*
