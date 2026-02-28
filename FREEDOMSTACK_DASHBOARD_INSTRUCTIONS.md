# FreedomStack — Dashboard Implementation Instructions

> **Purpose:** This document defines the exact behavior of the authenticated dashboard. Feed this to the Cursor agent when building the dashboard home screen.
>
> **References:** `FREEDOMSTACK_USER_JOURNEY.md` (full user journey), `FREEDOMSTACK_CURSOR_INSTRUCTIONS.md` (technical architecture)
>
> **Prototype:** `freedomstack-dashboard-v2.html` (interactive reference for visual design)

---

## CORE PRINCIPLE: DATA-DRIVEN ADAPTIVE DASHBOARD

The dashboard is ONE layout that adapts based on **profile completeness and data availability**. There is NO separate "new user" vs "returning user" view. Every card and module renders based on what data exists in the database for that user.

**Rules:**
- Never display fake, placeholder, or hardcoded numbers. If data doesn't exist, show the empty state.
- Every empty state must explain what's missing and link directly to where the user can provide it.
- The dashboard should feel useful from day one (even with partial data) and become more powerful as the user completes their profile.

---

## 1. PROFILE COMPLETENESS MODULE

### When to Show
Display this module at the top of the dashboard (below the welcome message) **whenever the user's profile is incomplete**. Hide it completely when all steps are done.

### Completeness Calculation
Profile completeness is based on 5 data checkpoints:

| Step | Check | Data Source |
|------|-------|-------------|
| 1. Expenses | `expenses` table has at least 1 record for this user OR `freedom_calculations` exists | `expenses` table or `freedom_calculations` |
| 2. Freedom Number | `freedom_calculations` table has at least 1 record | `freedom_calculations` |
| 3. Income details | `financial_profiles.monthly_gross_income` is not null AND > 0 | `financial_profiles` |
| 4. Debts | `debts` table has at least 1 record for this user, OR user explicitly marked "no debts" | `debts` table |
| 5. Savings & Investments | `investment_accounts` table has at least 1 record for this user, OR user explicitly marked "no investments" | `investment_accounts` table |

**Percentage:** `(completed_steps / 5) * 100`

### UI Layout
```
┌──────────────────────────────────────────────────────────────┐
│ Complete your profile to unlock your full action plan    40%  │
│ [████████░░░░░░░░░░░░]                                       │
│                                                              │
│ ✓ Expenses          ✓ Freedom Number    ○ Income details     │
│   Monthly expenses    Calculated at       Needed for         │
│   entered             $960,000            timeline calc      │
│                                           ~1 min             │
│                                                              │
│ ○ Add your debts                  ○ Add savings &            │
│   Needed for payoff strategy        investments              │
│   ~2 min                            Needed for net worth     │
│                                     ~2 min                   │
└──────────────────────────────────────────────────────────────┘
```

### Behavior
- Completed steps show a green checkmark icon and reduced opacity (0.5)
- Pending steps are clickable — clicking navigates to the relevant page (Profile, Debts, or Investments)
- Each pending step shows: step title, WHY it's needed (one line), and estimated time
- Progress bar fills proportionally with green
- When all 5 steps are complete: hide this entire module, trigger AI recommendation generation if not already queued
- Use a responsive grid: 5 cards in a row on desktop, stack on mobile

### "No Debts" / "No Investments" Handling
On the Debts and Investments pages, provide a "I don't have any debts" / "I don't have any savings yet" option. If selected, mark that step as complete in the profile completeness check. Store this as a boolean flag on `financial_profiles`: `has_no_debts`, `has_no_investments`. If the user later adds a debt or account, automatically flip the flag.

---

## 2. WELCOME MESSAGE

### Logic
```
if (user.last_login is NULL or user created within last 24 hours):
    "Welcome to FreedomStack, {first_name}"
    "Your Freedom Number is set. Complete your profile to unlock personalized recommendations."
else:
    "Welcome back, {first_name}"
    "Here's where you stand as of {current_month} {current_year}."
```

---

## 3. STAT CARDS ROW (4 cards)

Each stat card adapts independently based on whether the data needed to compute it exists.

### Card 1: Freedom Number
- **Source:** `freedom_calculations.freedom_number` (latest record)
- **Always available** — user calculated this before registering
- **Display:** `$960,000` in green, subtitle "at 4% safe withdrawal rate"
- **Delta:** None (this is a target, not a tracked metric). Static.

### Card 2: Net Worth
- **Source:** Calculated: `SUM(investment_accounts.balance) - SUM(debts.balance)`
- **Requires:** At least one record in `debts` OR `investment_accounts`
- **If data exists:**
  - Show calculated net worth (can be negative)
  - Delta: compare to previous month's `progress_snapshots.net_worth`
  - If no previous snapshot: show "First snapshot — track monthly to see changes"
  - If negative: display in default white (not red — avoid shame). Show context naturally.
- **If no data:**
  - Value: "—" (em dash) in muted gray
  - Card gets dashed border (`border-style: dashed`)
  - Delta area shows: "Add debts & savings to calculate →" as a green clickable link to My Finances > Debts
  
### Card 3: Freedom Progress
- **Source:** Calculated: `(net_worth / freedom_number) * 100`
- **Requires:** Net worth to be calculable (at least one debt or investment record) AND freedom number
- **If data exists:**
  - Show percentage with 1 decimal: "2.9%"
  - Delta: compare to previous month's `progress_snapshots.freedom_pct_achieved`
  - If no previous snapshot: "Log first check-in to track changes"
- **If no data:**
  - Value: "—" in muted gray
  - Delta: "Needs net worth data" in gray (no link — resolves when net worth card resolves)

### Card 4: Time to Freedom
- **Source:** `freedom_calculations.years_to_freedom` (latest) — requires income and savings rate to calculate
- **Requires:** `financial_profiles.monthly_gross_income` AND `financial_profiles.monthly_net_income` AND at least one investment account with a monthly contribution, AND freedom number
- **If data exists:**
  - Show: "18 yr 4 mo" format
  - Delta: compare to previous month's `progress_snapshots.estimated_months_to_freedom`
  - If no previous snapshot: "With all recommended levers enabled"
- **If no data:**
  - Value: "—" in muted gray
  - Dashed border on card
  - Delta: "Add income to calculate →" as green clickable link to My Finances > Profile

### Delta Formatting Rules
- Positive change (improvement): green text, "↑" prefix
- Negative change (regression): red text, "↓" prefix
- No change: gray text, "—" or "No change"
- No prior data for comparison: gray italic text explaining why

---

## 4. CHECK-IN NUDGE CARD

### Logic
```
latest_snapshot = most recent progress_snapshots for this user

if (profile is incomplete — any of the 5 steps not done):
    Show green "ok" card:
    Title: "You're just getting started"
    Subtitle: "Complete your profile first. Monthly check-ins begin after your first full month."
    Button: "Got it" (green, dismisses card for session)

else if (latest_snapshot is NULL):
    Show amber "nudge" card:
    Title: "Log your first check-in"
    Subtitle: "Record your starting point so we can track your progress. Takes 60 seconds."
    Button: "Log now" (amber, navigates to Progress page)

else if (days since latest_snapshot.snapshot_date >= 25):
    Show amber "nudge" card:
    Title: "Time for your {current_month} check-in"
    Subtitle: "Last updated {snapshot_date formatted}. Takes about 60 seconds."
    Button: "Update now" (amber, navigates to Progress page)

else:
    Show green "ok" card:
    Title: "You're up to date"
    Subtitle: "Next check-in: {snapshot_date + 30 days formatted}"
    Button: none (or subtle "View progress →" link)
```

---

## 5. CONTENT GRID (2x2)

### Card: Your Journey (top left)
- **Source:** `progress_snapshots` — all records for user, ordered by `snapshot_date`
- **Chart type:** ApexCharts area chart
- **X-axis:** Months (snapshot dates)
- **Y-axis:** Dollar amount
- **Lines:** Net worth (primary green area), Freedom Number (horizontal dashed target line)
- **If < 2 snapshots:** Show placeholder message: "Log monthly check-ins to see your progress over time. Your first data point starts the chart."
- **Link:** "View all →" navigates to Progress page

### Card: Monthly Expenses (top right)
- **Source:** `expenses` table, grouped by category
- **Chart type:** ApexCharts donut chart
- **Always available** — user entered expenses during calculator flow
- **Center text:** Total monthly amount
- **Legend:** Top 4 categories by amount, remainder grouped as "Other"
- **Link:** "Manage →" navigates to My Finances > Expenses

### Card: Top Recommendations (bottom left)
- **Source:** `ai_recommendations` — latest completed recommendation set
- **If recommendations exist:**
  - Show top 3 as compact list items: priority number, title, timeline impact
  - Each item clickable → navigates to Action Plan page
  - Link: "Full plan →" navigates to Action Plan
- **If no recommendations AND profile is complete:**
  - Show loading state: "Generating your action plan..." with subtle spinner
  - This means the AI job is queued/processing
- **If no recommendations AND profile is incomplete:**
  - Show locked state with lock icon
  - Title: "Complete your profile to unlock"
  - Subtitle: "We need your income, debts, and savings to generate personalized recommendations."
  - No link (resolves via profile completeness module above)

### Card: Debt Snapshot (bottom right)
- **Source:** `debts` table — all records for user
- **If debts exist:**
  - Show each debt as a horizontal bar: name, remaining balance, colored by APR (high >15% = red, 5-15% = amber, <5% = green)
  - Bar width = `remaining_balance / original_balance * 100` (shows payoff progress)
  - Total at bottom: "Total remaining: $X"
  - Link: "Details →" navigates to My Finances > Debts
- **If no debts and `has_no_debts` flag is true:**
  - Show: "No debts — that's a strong starting position."
- **If no debts and flag is false:**
  - Show empty state: "No debts added yet"
  - Subtitle: "Add your debts to see payoff strategies and optimization opportunities."
  - Link: "Add your first debt →" navigates to My Finances > Debts

---

## 6. SIDEBAR NAVIGATION BADGES

Badges on sidebar nav items should update dynamically:

| Nav Item | Badge | Condition |
|----------|-------|-----------|
| Action Plan | Green badge with count (e.g., "3") | Number of active recommendations. Hide badge if 0 or if no recommendations generated yet. |
| Progress | Amber "Update" badge | Show when days since last snapshot >= 25. Hide when up to date or profile incomplete. |
| My Finances | Amber dot (no text) | Show when profile completeness < 100%. Hide when complete. |

---

## 7. AI RECOMMENDATION TRIGGER

The AI recommendation generation should be triggered automatically when:
1. All 5 profile completeness steps are done for the first time → Queue the `GenerateRecommendations` job immediately
2. User completes a monthly check-in with >10% change in any major metric (income, total debt, total savings) → Queue a refresh
3. User manually clicks "Refresh Recommendations" on the Action Plan page (subject to daily rate limit)

Do NOT trigger AI generation if the profile is incomplete. The user must have: expenses, freedom number, income, and at least debts OR investments.

---

## 8. IMPLEMENTATION PRIORITY

Build the dashboard in this order:
1. Welcome message (conditional text)
2. Stat cards row (with all empty/populated states)
3. Profile completeness module (with progress bar and clickable steps)
4. Check-in nudge card (with all conditional states)
5. Monthly expenses donut (always available)
6. Debt snapshot card (with empty states)
7. Recommendations card (with locked/loading/populated states)
8. Journey chart (with empty state, populate later when progress tracking is built)
9. Sidebar badge logic

---

## 9. DESIGN REFERENCE

Use the interactive prototype `freedomstack-dashboard-v2.html` as the visual reference for:
- Card styling (background, borders, border-radius, padding)
- Stat card layout (icon position, value size, delta formatting)
- Color coding (green for positive, red for negative, amber for warnings, gray for missing data)
- Dashed border treatment for cards missing data
- Profile completeness step card layout
- Check-in card layout and color variants
- Empty state messaging style

Adapt all visuals to Vuexy's component library (use Vuexy's card components, chart components, badge components). Match the prototype's color scheme and spacing but use Vuexy's design system primitives.

---

## 10. DATA AVAILABILITY MATRIX

Quick reference for what's available at each stage:

| User State | Expenses | Freedom # | Income | Debts | Investments | Net Worth | Timeline | AI Recs |
|---|---|---|---|---|---|---|---|---|
| Just registered from calculator | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Added income | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | Partial | ✗ |
| Added debts | ✓ | ✓ | ✓ | ✓ | ✗ | Partial (negative) | Partial | ✗ |
| Added investments | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | Generating... |
| Full profile, recs ready | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Returning user (monthly) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ + deltas | ✓ + deltas | ✓ (may refresh) |

The dashboard renders correctly at EVERY row of this table. No row should produce a broken, empty, or confusing UI.

---

*This document defines dashboard behavior. See `FREEDOMSTACK_USER_JOURNEY.md` for overall user flow and `FREEDOMSTACK_CURSOR_INSTRUCTIONS.md` for technical implementation patterns.*
