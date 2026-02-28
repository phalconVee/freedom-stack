# FreedomStack — User Journey & UI Specification

> **Purpose:** This document defines every screen, interaction, and user flow in FreedomStack. It is the product blueprint the Cursor agent must follow when building frontend views and backend logic.
>
> **Companion to:** `CURSOR.md` (technical/architectural instructions)
>
> **Rule:** When building any UI or user-facing feature, reference THIS document first for what to build, then the technical instructions for how to build it.

---

## TABLE OF CONTENTS

1. [User Journey Overview](#1-user-journey-overview)
2. [Public Experience (No Account)](#2-public-experience-no-account)
3. [Account Creation & Onboarding](#3-account-creation--onboarding)
4. [Authenticated Dashboard Experience](#4-authenticated-dashboard-experience)
5. [Left Sidebar Navigation](#5-left-sidebar-navigation)
6. [Screen-by-Screen Specifications](#6-screen-by-screen-specifications)
7. [Module Deep Dives](#7-module-deep-dives)
8. [Notifications & Nudges](#8-notifications--nudges)
9. [Mobile Responsiveness](#9-mobile-responsiveness)
10. [Tone & Copy Guidelines](#10-tone--copy-guidelines)

---

## 1. USER JOURNEY OVERVIEW

### The Two Funnels

FreedomStack has two distinct user experiences that merge at the account creation point:

```
FUNNEL A: PUBLIC (No Account)                  FUNNEL B: AUTHENTICATED (Account)
─────────────────────────────                  ─────────────────────────────────
                                               
1. Landing Page                                1. Login
2. Freedom Number Calculator                   2. Dashboard (Home)
3. Results Screen (gut-punch moment)           3. All modules accessible
4. "Want to save + get your action plan?"      
   ├── Yes → Register → Onboarding → Dashboard
   └── No → Can recalculate, but data is lost
```

### The Complete Journey (First-Time User, End to End)

```
Landing Page
    │
    ▼
Freedom Number Calculator (public, no account needed)
    │  User enters monthly expenses by category
    │  Selects lifestyle level (basics / comfortable / full freedom)
    │
    ▼
Freedom Number Results
    │  Sees their number at 4%, 5%, 6% withdrawal rates
    │  Balanced tone: "Your freedom number is $960,000.
    │  With the right strategy, most people with your profile
    │  reach this in 15-20 years. Let's build your plan."
    │
    ▼
Call to Action: "Save your number & get a personalized action plan"
    │
    ├── User clicks → Registration screen (email + password, or OAuth)
    │
    ▼
Onboarding Flow (4 steps, skippable but encouraged)
    │  Step 1: Basic Info (age, filing status, state)
    │  Step 2: Income (gross, net, employer)
    │  Step 3: Debts (type, balance, rate, minimum payment — add multiple)
    │  Step 4: Assets/Investments (401K, IRA, HYSA, savings — add multiple)
    │
    ▼
"The Gap" Screen
    │  Shows: current net worth vs. freedom number
    │  Shows: years to freedom at current trajectory
    │  Shows: "Let's close this gap. Generating your action plan..."
    │
    ▼
AI Action Plan (generated async, ~5-15 seconds)
    │  Personalized recommendations with lever toggling
    │  Each lever shows timeline impact
    │
    ▼
Dashboard (Home) — the user's ongoing hub
    │  Progress tracking, quick stats, recent recommendations
    │
    ▼
Return Visits
    │  Monthly update → dashboard refreshes → AI re-evaluates if needed
```

---

## 2. PUBLIC EXPERIENCE (NO ACCOUNT)

### 2.1 Landing Page

**URL:** `/`

**Layout:** Full-width marketing page, NOT the dashboard layout. No sidebar. Clean, modern, single-page scroll.

**Sections (top to bottom):**

#### Hero Section
- **Headline:** "How Much Do You Need to Never Worry About Money Again?"
- **Subheadline:** "Calculate your Freedom Number in 60 seconds — the exact amount you need invested to cover your life, forever."
- **Primary CTA Button:** "Calculate My Freedom Number" → scrolls to or navigates to calculator
- **Secondary text:** "Free. No account required. No credit card."
- **Visual:** Abstract illustration or animation of a number growing (not stock photos of smiling people with laptops)

#### How It Works (3 steps)
- **Step 1:** "Tell us your monthly expenses" — icon: receipt/list
- **Step 2:** "See your Freedom Number" — icon: target/bullseye
- **Step 3:** "Get your personalized action plan" — icon: map/compass
- Keep this tight. 3 cards in a row. Minimal text.

#### Social Proof / Trust (Phase 2 — placeholder for now)
- "Built by someone paying off MBA debt, for people paying off MBA debt."
- Placeholder for user count, testimonials once available

#### Footer
- About, Privacy Policy, Terms, "Not financial advice" disclaimer
- "Built with ☕ and student loan anxiety"

**Technical notes:**
- This page should be a standalone Vue component, NOT inside the Vuexy dashboard layout
- Use Vuexy's color palette and typography but a custom layout
- Must be fast — no heavy JS on this page
- SEO-friendly: server-renderable or at minimum proper meta tags

---

### 2.2 Freedom Number Calculator (Public)

**URL:** `/calculator`

**Layout:** Centered content, clean background, no sidebar. Can be a full-page form or a step-by-step wizard. Use Vuexy's **Form Wizard / Stepper** component adapted to a public (non-dashboard) layout.

**This is the most important conversion screen.** It must feel simple, fast, and non-threatening.

#### Step 1: Monthly Expenses

**Header:** "What does your life cost each month?"

**Two input modes** (user can toggle between them):

**Simple Mode (default):**
- Single input field: "Total monthly expenses" with a dollar input
- Helper text: "Include rent, food, bills, subscriptions, loan payments — everything."
- Pre-filled placeholder: $3,500

**Detailed Mode:**
- Category-by-category breakdown with individual dollar inputs
- Categories:

| Category | Icon | Placeholder | Essential? |
|----------|------|-------------|------------|
| Housing (rent/mortgage) | 🏠 | $1,500 | Yes |
| Transportation | 🚗 | $400 | Yes |
| Food & Groceries | 🛒 | $500 | Yes |
| Insurance | 🛡️ | $200 | Yes |
| Healthcare | ⚕️ | $150 | Yes |
| Utilities | 💡 | $200 | Yes |
| Debt Payments | 💳 | $500 | Yes |
| Personal & Shopping | 🛍️ | $200 | No |
| Entertainment & Dining Out | 🎬 | $200 | No |
| Education | 📚 | $0 | No |
| Savings & Investing | 💰 | $0 | No |
| Other | ➕ | $0 | No |

- Running total displayed at bottom as user fills in categories
- Each category has an "essential" toggle (pre-set but editable)

**Bottom of step:** "Next" button

#### Step 2: What "Freedom" Means to You

**Header:** "What does financial freedom look like for you?"

Three cards the user can select (radio-style, only one):

**Card 1 — "Just the Basics"**
- Description: "Cover essential expenses only — housing, food, insurance, healthcare, utilities."
- Uses only the essential-flagged expenses from Step 1
- Icon: Simple/minimal lifestyle illustration

**Card 2 — "Comfortable" (pre-selected default)**
- Description: "Cover everything you spend now, including entertainment and personal spending."
- Uses total expenses from Step 1
- Icon: Balanced lifestyle illustration

**Card 3 — "Full Freedom"**
- Description: "Cover current expenses plus a 20% buffer for travel, hobbies, and the unexpected."
- Uses total expenses × 1.2
- Icon: Abundant lifestyle illustration

**Bottom of step:** "Show My Number" button (primary, prominent)

---

### 2.3 Freedom Number Results Screen

**URL:** `/calculator/results` (or a state within `/calculator`)

**THIS IS THE KEY EMOTIONAL MOMENT OF THE PRODUCT.**

**Layout:** Centered, dramatic, with breathing room. No clutter.

#### The Big Number

Center of screen, large typography (48-72px on desktop):

```
Your Freedom Number

$960,000
────────────────────
at a 4% safe withdrawal rate
```

**Below the number, immediately — the balanced reframe:**

> "This might feel like a big number. Here's the thing — with a solid strategy, people in similar positions typically build this in 15-20 years. Some do it faster. Let's see where you stand."

#### The Range View

Below the reframe, show a horizontal bar or card row with three rates:

| Conservative (4%) | Moderate (5%) | Optimistic (6%) |
|---|---|---|
| **$960,000** | **$768,000** | **$640,000** |
| Safest — survives market downturns | Balanced — typical diversified portfolio | Aggressive — assumes strong returns |

The 4% number should be visually emphasized as the "recommended" default with a small badge or highlight.

#### What These Numbers Mean (Expandable)

A collapsible "Learn more" section explaining:
- "The 4% rule comes from the Trinity Study — it's the amount you can withdraw annually from a diversified portfolio with a very high probability of never running out of money over 30+ years."
- "Conservative is safest. Moderate assumes a balanced portfolio. Optimistic assumes strong equity returns."
- Link to deeper educational content (future Learn module)

#### Call to Action

Two options presented:

**Primary CTA (prominent button):**
"Get Your Personalized Action Plan →"
- Subtext: "Free account required. We'll analyze your income, debts, and investments to show you exactly how to reach this number."
- This triggers the registration flow

**Secondary CTA (text link):**
"Recalculate with different numbers"
- Returns to the calculator with previous inputs pre-filled

**Tertiary CTA (text link):**
"Share my Freedom Number" — generates a shareable card/link (Phase 2)

---

## 3. ACCOUNT CREATION & ONBOARDING

### 3.1 Registration

**URL:** `/register`

**Trigger:** User clicks "Get Your Personalized Action Plan" from results screen.

**Layout:** Clean auth page using Vuexy's pre-built registration component.

**Fields:**
- Full name
- Email address
- Password (with strength indicator)
- Checkbox: "I understand this is for informational purposes only and is not financial advice."

**OAuth options (Phase 2):** Google, Apple Sign-In

**After registration:**
- User is immediately logged in
- Their calculator results from the public session are transferred to their new account (store in sessionStorage/localStorage before registration, then persist to DB on account creation)
- Redirect to Onboarding Step 1

---

### 3.2 Onboarding Flow

**URL:** `/onboarding` (with step indicator: `/onboarding?step=1`)

**Layout:** Centered content area with progress indicator at top. Use Vuexy's **Stepper** component. No sidebar yet — the sidebar appears only after onboarding is complete.

**Progress Bar:** "Step 1 of 4 — Let's build your financial profile"

**ALL STEPS ARE SKIPPABLE** — user can click "Skip for now" on any step and fill in later. But encourage completion: "The more we know, the better your action plan."

#### Onboarding Step 1: About You

**Header:** "First, a bit about you"

Fields:
- **Current age** — number input (required)
- **Target freedom age** — number input with helper: "What age would you like to be financially free?" Default: current age + 20
- **Filing status** — dropdown: Single, Married Filing Jointly, Married Filing Separately, Head of Household
- **State of residence** — dropdown (all 50 states + DC)
- **Risk tolerance** — 3 cards (like the lifestyle selector):
  - Conservative: "I prefer safety over growth. I'd rather earn less than risk losing money."
  - Moderate: "I'm okay with some ups and downs for better long-term returns." (default)
  - Aggressive: "I'm comfortable with high volatility for maximum growth potential."

**Button:** "Next: Income →"
**Skip link:** "Skip for now"

#### Onboarding Step 2: Income

**Header:** "What's coming in?"

Fields:
- **Monthly gross income** — dollar input (before taxes)
- **Monthly net income** — dollar input (take-home pay)
- **Income type** — radio: Salaried, Hourly, Self-employed, Mixed
- **Side income (optional)** — dollar input with label: "Freelance, side hustle, rental income, etc."

**Contextual tip:** "If you're not sure about your exact net income, check your last bank statement or pay stub."

**Button:** "Next: Debts →"
**Skip link:** "Skip for now"

#### Onboarding Step 3: Debts

**Header:** "What do you owe?"

**Layout:** A list where the user can add multiple debts. Each debt is a card that expands for detail.

**"Add a Debt" button** opens a form card:
- **Debt type** — dropdown: Student Loan, Credit Card, Auto Loan, Mortgage, Personal Loan, Medical Debt, Other
- **Name/Label** — text input, placeholder: "e.g., Sallie Mae Federal Loan"
- **Current balance** — dollar input
- **Interest rate (APR)** — percentage input
- **Minimum monthly payment** — dollar input
- **Is this a federal student loan?** — toggle (only shown if type = Student Loan)

User can add as many debts as needed. Each added debt appears as a summary card:
```
┌──────────────────────────────────────┐
│ 🎓 Sallie Mae Federal Loan          │
│ Balance: $42,000 | APR: 6.5%        │
│ Min Payment: $450/mo                 │
│ [Edit] [Remove]                      │
└──────────────────────────────────────┘
```

**Running total at bottom:** "Total debt: $62,000 | Total monthly payments: $750"

**If user has no debts:** "No debts? That's amazing. Click next to continue."

**Button:** "Next: Savings & Investments →"
**Skip link:** "Skip for now"

#### Onboarding Step 4: Savings & Investments

**Header:** "What have you built so far?"

Same pattern as debts — a list of accounts the user can add.

**"Add an Account" button** opens a form card:
- **Account type** — dropdown: 401(k), 403(b), Traditional IRA, Roth IRA, HSA, Brokerage, HYSA/Savings, Other
- **Name/Label** — text input, placeholder: "e.g., Fidelity 401K"
- **Current balance** — dollar input
- **Monthly contribution** — dollar input (how much they add per month)
- **Employer match %** — percentage input (only shown for 401K/403B types)
- **Employer match limit** — dollar input (only shown if match % > 0)

Summary cards same style as debts.

**Running total at bottom:** "Total invested/saved: $28,000 | Monthly contributions: $600"

**Button:** "See My Action Plan →" (primary, exciting)
**Skip link:** "Skip for now, take me to the dashboard"

---

### 3.3 The Gap Screen (Transition)

**URL:** `/gap-analysis` (brief transitional screen)

**Layout:** Centered, cinematic. This is the emotional bridge between onboarding and the AI recommendations.

**Content:**

Top section — two large numbers side by side:

```
Where You Are                    Where You Need to Be
───────────                      ────────────────────
   $28,000           ──→──          $960,000
  (net worth)                    (freedom number)
```

**Visual:** A progress bar or bridge graphic showing how far along they are (e.g., 2.9% complete).

**Below:**

> "At your current savings rate of $600/month, you'd reach your Freedom Number in **52 years**."
>
> "But that's the baseline — without any optimization. Let's find the moves that change everything."

**Loading indicator:**
"🧠 Analyzing your profile and generating your personalized action plan..."

This screen shows for 5-15 seconds while the AI recommendation job runs. Use a subtle animation — NOT a boring spinner. Consider:
- A step-by-step progress ("Analyzing debts... Optimizing contributions... Building your plan...")
- A financial tip carousel while waiting
- An animated path/road that fills in

**When AI results are ready:** Auto-redirect to the AI Action Plan screen.

**If AI takes longer than 15 seconds:** Show: "Still working on your plan. You can wait or head to your dashboard — we'll notify you when it's ready." with a button to go to dashboard.

---

## 4. AUTHENTICATED DASHBOARD EXPERIENCE

### Layout Structure

Once the user is authenticated and has completed onboarding (or skipped it), they enter the **dashboard layout**. This uses Vuexy's standard admin layout:

```
┌────────────────────────────────────────────────────────────┐
│  Top Navbar: Logo | Search (future) | Notifications | Profile Avatar │
├──────────┬─────────────────────────────────────────────────┤
│          │                                                  │
│  Left    │                                                  │
│  Sidebar │            Main Content Area                     │
│  Nav     │                                                  │
│          │                                                  │
│  (see    │            (changes per module)                   │
│  Section │                                                  │
│  5)      │                                                  │
│          │                                                  │
├──────────┴─────────────────────────────────────────────────┤
│  Footer: "Not financial advice" disclaimer | © FreedomStack │
└────────────────────────────────────────────────────────────┘
```

---

## 5. LEFT SIDEBAR NAVIGATION

### Navigation Structure

The sidebar uses Vuexy's collapsible nav component. Items are grouped into logical sections with divider labels.

```
FREEDOMSTACK (logo)
────────────────────

📊  Dashboard              ← Home screen, overview of everything
    
PLAN
────
🧮  Calculator             ← Recalculate freedom number anytime
💰  My Finances            ← Manage profile, expenses, debts, assets
    ├── Profile
    ├── Expenses
    ├── Debts
    └── Investments
🤖  Action Plan            ← AI-generated recommendations
⚡  Scenarios              ← "What-if" modeling (Phase 2)

TRACK
─────
📈  Progress               ← Monthly tracking, net worth over time

LEARN
─────
📚  Resources              ← Financial education content (Phase 2)

────────────────────
⚙️  Settings               ← Account, preferences, subscription
```

### Navigation States

- **Active item:** Highlighted with Vuexy's primary color, bold text
- **Collapsed sidebar:** Icons only, hover to expand (Vuexy built-in behavior)
- **My Finances sub-menu:** Collapsed by default, expands on click
- **Phase 2 items (Scenarios, Resources):** Show in nav with a "Coming Soon" badge. Clicking shows a teaser page with email capture for launch notification.
- **Mobile:** Sidebar becomes a hamburger menu overlay

### Badge Indicators

- **Action Plan:** Show a notification dot when new recommendations are available
- **Progress:** Show a nudge dot if user hasn't logged a monthly update in 30+ days
- **Dashboard:** Show count badge for any pending actions

---

## 6. SCREEN-BY-SCREEN SPECIFICATIONS

### 6.1 Dashboard (Home)

**URL:** `/dashboard`
**Nav item:** 📊 Dashboard

**Purpose:** At-a-glance view of the user's financial freedom journey. This is what they see every time they log in.

**Layout:** Use Vuexy's dashboard grid layout. Cards arranged in a responsive grid.

#### Row 1: Hero Stats (4 cards in a row)

```
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
│ Freedom Number   │ │ Net Worth       │ │ Progress         │ │ Time to Freedom  │
│ $960,000         │ │ -$34,000        │ │ 2.9%             │ │ 18 yrs, 4 mo    │
│ at 4% SWR        │ │ ↑ $2,400        │ │ ████░░░░░░░░░░░ │ │ ↓ 3 mo faster   │
│                   │ │ since last month│ │                   │ │ than last month  │
└─────────────────┘ └─────────────────┘ └─────────────────┘ └─────────────────┘
```

Use Vuexy's **Statistics Cards** component. Each card:
- Large number (primary metric)
- Subtitle (context)
- Small delta indicator (↑ or ↓ since last snapshot, with green/red coloring)
- Relevant icon

**Net worth note:** If negative (debts > assets), show in red but with encouraging context: "This is your starting point. It gets better."

#### Row 2: Charts (2 cards, equal width)

**Left card — "Your Journey" (line/area chart):**
- X-axis: months (since first snapshot)
- Y-axis: dollar amount
- Lines: Net worth (primary), Freedom Number (horizontal target line, dashed)
- If user has < 2 snapshots, show a prompt: "Log your first monthly update to start tracking"
- Use ApexCharts area chart with Vuexy's color scheme

**Right card — "Where Your Money Goes" (donut chart):**
- Breakdown of monthly expenses by category
- Interactive: hover to see amount and percentage
- Use ApexCharts donut chart

#### Row 3: Action Items (full width)

**Card — "Your Next Moves":**
- Shows top 3 AI recommendations as a compact list
- Each item: priority number, title, timeline impact ("saves 2.5 years"), and a "View Details" link
- "View Full Action Plan →" link at bottom
- If no recommendations generated yet: CTA to complete their profile

#### Row 4: Quick Actions (2-3 cards)

**Card — "Monthly Check-In":**
- If 25+ days since last snapshot: "Time for your monthly update! (Takes 60 seconds)" with CTA button
- If recently updated: "You're up to date ✓ Next check-in: March 15"

**Card — "Debt Payoff Progress" (if user has debts):**
- Small horizontal bar chart showing each debt's remaining balance
- Total debt amount and monthly change
- "X debts remaining | $Y paid off so far"

**Card — "Quick Recalculate" (compact):**
- "Life changed? Recalculate your Freedom Number →" link to calculator

---

### 6.2 Calculator

**URL:** `/calculator` (authenticated version)
**Nav item:** 🧮 Calculator

**Purpose:** Same calculator as the public version, but within the dashboard layout and with auto-save.

**Differences from public calculator:**
- Pre-fills expenses from saved data (if available)
- Auto-saves calculation results to history
- Shows calculation history at bottom: "Previous calculations" with date, expenses used, and resulting freedom numbers
- Can save multiple scenarios with labels

**Layout:** Same form wizard as public, but wrapped in the Vuexy dashboard layout with sidebar visible.

---

### 6.3 My Finances — Profile

**URL:** `/finances/profile`
**Nav item:** 💰 My Finances → Profile

**Purpose:** Manage personal and income information.

**Layout:** Use Vuexy's **Account Settings** page template as the base.

**Sections:**

**Personal Information Card:**
- Name, email, age, filing status, state, risk tolerance
- All editable inline with save button

**Income Card:**
- Monthly gross income, monthly net income, income type, side income
- All editable inline with save button

**Freedom Preferences Card:**
- Target freedom age
- Preferred withdrawal rate (4%, 5%, 6% — radio)
- Lifestyle level (basics, comfortable, full freedom)
- Inflation assumption (default 3%, adjustable)

---

### 6.4 My Finances — Expenses

**URL:** `/finances/expenses`
**Nav item:** 💰 My Finances → Expenses

**Purpose:** Manage monthly expense categories.

**Layout:** Use Vuexy's **DataTable** component for the expense list.

**Features:**
- Table view: Category | Name | Monthly Amount | Essential? | Actions (edit/delete)
- "Add Expense" button opens a modal form (same fields as onboarding)
- Sort by: amount (high-low), category, essential-first
- Summary row at bottom: "Total: $3,200/mo | Essential: $2,450 | Discretionary: $750"
- Inline edit capability (click a cell to edit)
- Bulk actions: delete selected, mark as essential/discretionary

---

### 6.5 My Finances — Debts

**URL:** `/finances/debts`
**Nav item:** 💰 My Finances → Debts

**Purpose:** Manage all debts and view payoff strategies.

**Layout:** Card-based list (same as onboarding) + analysis section below.

**Top section — Debt List:**
- Each debt is a card with: type icon, name, balance, APR, min payment, federal flag
- "Add Debt" button opens modal
- Edit/delete on each card

**Bottom section — Debt Analysis:**

**Payoff Comparison Card (auto-calculated by PHP engine):**

```
┌────────────────────────────────────────────────────────┐
│  Debt Payoff Strategy Comparison                        │
│                                                         │
│  Avalanche (recommended)    │    Snowball               │
│  ─────────────────────      │    ─────────              │
│  Pay off in: 4 yr 2 mo      │    Pay off in: 4 yr 8 mo  │
│  Total interest: $8,420      │    Total interest: $9,180  │
│  You save: $760              │                            │
│                                                         │
│  Payoff order:               │    Payoff order:           │
│  1. Credit Card (19.9%)      │    1. Medical ($2,400)     │
│  2. Personal Loan (8.5%)     │    2. Auto Loan ($8,000)   │
│  3. Student Loan (6.5%)      │    3. Credit Card ($5,500) │
│  4. Auto Loan (4.2%)         │    4. Student Loan ($42K)  │
│                                                         │
│  [View Full Payoff Schedule]                             │
└────────────────────────────────────────────────────────┘
```

**Payoff Schedule (expandable):**
- Month-by-month table: Month | Debt Being Targeted | Payment | Remaining Balance | Interest Paid
- Visual: stacked area chart showing each debt's balance over time declining to zero

---

### 6.6 My Finances — Investments

**URL:** `/finances/investments`
**Nav item:** 💰 My Finances → Investments

**Purpose:** Manage all savings and investment accounts.

**Layout:** Card-based list + optimization analysis.

**Top section — Account List:**
- Each account is a card: type icon, name, balance, monthly contribution, employer match info
- "Add Account" button opens modal

**Bottom section — Optimization Analysis (auto-calculated):**

**Contribution Priority Card:**
```
┌────────────────────────────────────────────────────────┐
│  Recommended Contribution Order                         │
│                                                         │
│  1. ✅ 401K up to employer match ($3,000/yr matched)   │
│     Status: You're contributing $200/mo. Match kicks    │
│     in at $250/mo. You're leaving $600/year on the     │
│     table in free money.                                │
│                                                         │
│  2. ⬜ HSA (if eligible)                                │
│     Status: No HSA detected. [Learn why this matters]   │
│                                                         │
│  3. ⬜ Roth IRA (up to $7,000/yr limit)                │
│     Status: Not yet opened. [How to start]              │
│                                                         │
│  4. ⬜ 401K to maximum ($23,500/yr limit)              │
│     Status: Currently at $2,400/yr of $23,500           │
│                                                         │
│  5. ⬜ Taxable brokerage                                │
│     Status: Any remaining savings go here               │
└────────────────────────────────────────────────────────┘
```

---

### 6.7 Action Plan (AI Recommendations)

**URL:** `/action-plan`
**Nav item:** 🤖 Action Plan

**Purpose:** Display AI-generated personalized recommendations with interactive timeline impact.

**THIS IS THE PRODUCT'S CORE DIFFERENTIATOR.**

**Layout:** Two-column on desktop, single column on mobile.

#### Left Column (60% width) — Recommendations

**Header:** "Your Personalized Action Plan"
**Subheader:** "Generated [date] based on your financial profile. Each action shows how it moves your timeline."

**Summary Card (AI-generated):**
> "You're 28 with $62K in debt and $28K in assets. Your biggest opportunity right now is maximizing your employer 401K match — you're leaving $600/year in free money on the table. After that, aggressively paying down your 19.9% credit card will free up $200/month that can accelerate everything else."

**Recommendation Cards (ordered by priority):**

Each card is structured as:

```
┌─────────────────────────────────────────────────────────────┐
│ Priority #1                                    Saves 3.2 years │
│ ─────────────────────────────────────────────────────────── │
│ 🎯 Max Your 401K Employer Match                             │
│                                                              │
│ Why this matters:                                            │
│ Your employer matches 50% up to 6% of salary. You're only   │
│ contributing 3%. Bumping to 6% gives you $3,000/year in     │
│ free money — that's $600 you're currently leaving behind.   │
│                                                              │
│ What to do:                                                  │
│ ☐ Log into your 401K provider portal                        │
│ ☐ Increase contribution from 3% to 6% of gross salary       │
│ ☐ This costs you ~$106 more per paycheck                    │
│                                                              │
│ Timeline impact: Reaches freedom number 3.2 years faster     │
│ Confidence: High                                             │
│                                                              │
│ [ Enable This Lever ✓ ]                                      │
└─────────────────────────────────────────────────────────────┘
```

Each card has:
- Priority number
- Title
- Category tag (debt payoff / savings / retirement / income / expense reduction)
- "Why this matters" explanation (AI-generated narrative)
- "What to do" checklist (AI-generated specific actions)
- Timeline impact (pre-calculated by PHP, displayed by AI)
- Confidence level (high/medium/low with colored badge)
- **"Enable This Lever" toggle** — when toggled, it affects the right column timeline

Maximum 5 recommendation cards.

#### Right Column (40% width, sticky on scroll) — Timeline Impact

**"Your Freedom Timeline" Card (sticky):**

```
┌────────────────────────────────────────┐
│  Your Freedom Timeline                  │
│                                         │
│  Current trajectory:     52 years       │
│  With selected levers:   18.4 years     │
│                                         │
│  [═══════════░░░░░░░░░░░░░░░░] 35%     │
│  ↑ 33.6 years saved                    │
│                                         │
│  Enabled levers:                        │
│  ✅ Max 401K match        -3.2 yr       │
│  ✅ Pay off credit card   -2.8 yr       │
│  ✅ Increase income 10%   -8.5 yr       │
│  ☐ Reduce discretionary   -1.5 yr      │
│  ☐ Open Roth IRA          -1.2 yr      │
│                                         │
│  Target age: 46 (currently 28)          │
│                                         │
│  [Refresh Recommendations]              │
└────────────────────────────────────────┘
```

**Interaction:** When user toggles a lever on/off in the left column, this panel updates in real-time. The timeline recalculation uses **pre-computed PHP data stored with the recommendations** — NOT a new AI call. The frontend simply adds/removes the lever's pre-calculated impact.

**"Refresh Recommendations" button:** Triggers a new AI generation (subject to daily rate limit). Shows "Refreshing... (X of 10 daily refreshes used)"

---

### 6.8 Progress Tracker

**URL:** `/progress`
**Nav item:** 📈 Progress

**Purpose:** Monthly tracking of financial metrics over time.

**Layout:** Dashboard-style with charts and a data entry section.

#### Monthly Update Section (top)

If user hasn't updated in 25+ days, this is prominent:

```
┌────────────────────────────────────────────────────────────┐
│  📅 Monthly Check-In — February 2026                        │
│                                                              │
│  Last updated: January 18, 2026                              │
│  "Quick update — just change what's different"               │
│                                                              │
│  Net Worth     [-$34,000 ] (was -$36,400)                   │
│  Total Debt    [$60,500  ] (was $62,000)                    │
│  Total Saved   [$29,200  ] (was $28,000)                    │
│  Monthly Income[$5,100   ] (unchanged)                       │
│  Monthly Expenses[$3,200 ] (unchanged)                       │
│                                                              │
│  Notes: [________________________________]                   │
│  "Got a $200 raise this month"                               │
│                                                              │
│  [ Save Check-In ]                                           │
└────────────────────────────────────────────────────────────┘
```

**Key UX decisions:**
- All fields are pre-filled with the last snapshot's values
- User only changes what's different
- Green/red indicators show direction of change from last month
- Save should take under 60 seconds
- After save: celebration micro-interaction ("Nice! You reduced debt by $1,500 this month 🎉") and auto-recalculate freedom percentage

#### Charts Section (below)

**Chart 1 — Net Worth Over Time (full width):**
- Area chart with months on X-axis
- Shows net worth line with the freedom number as a horizontal target line
- Shaded area between current and target

**Chart 2 — Debt Reduction (half width):**
- Stacked bar chart showing each debt's balance over time
- Clear visual of debts shrinking month by month

**Chart 3 — Savings Growth (half width):**
- Stacked area chart of all investment accounts growing over time

**Chart 4 — Freedom Percentage (half width):**
- Radial progress chart showing % of freedom number achieved
- Below: small multiples showing percentage over last 6 months

#### History Table (bottom)

- Sortable table of all past snapshots
- Columns: Date | Net Worth | Debt | Savings | Income | Expenses | Savings Rate | Freedom %
- Export to CSV (Phase 2)

---

### 6.9 Scenarios (Phase 2 — Teaser)

**URL:** `/scenarios`
**Nav item:** ⚡ Scenarios

**Status:** Coming Soon teaser page.

**Content:**
```
┌────────────────────────────────────────────────────────────┐
│                                                              │
│  ⚡ Scenarios — Coming Soon                                 │
│                                                              │
│  "What if I get a 20% raise?"                               │
│  "What if I move to a cheaper city?"                        │
│  "What if I start a side business?"                         │
│  "What if I refinance my student loans?"                    │
│                                                              │
│  Model different life events and see how they impact         │
│  your Freedom Number and timeline in real-time.              │
│                                                              │
│  [Notify Me When It Launches]                                │
│  Email: [_______________] [Submit]                           │
│                                                              │
└────────────────────────────────────────────────────────────┘
```

**Future functionality (not for MVP):**
- Slider-based what-if tool
- "What if I increase income by $X?" → recalculate timeline
- "What if I move and save $500/mo on rent?" → recalculate
- "What if I refinance loans from 6.5% to 4.5%?" → recalculate
- Side-by-side scenario comparison

---

### 6.10 Resources (Phase 2 — Teaser)

**URL:** `/resources`
**Nav item:** 📚 Resources

**Status:** Coming Soon teaser page.

**Content:**
```
┌────────────────────────────────────────────────────────────┐
│                                                              │
│  📚 Financial Freedom Resources — Coming Soon               │
│                                                              │
│  Curated guides and explainers built for your situation:     │
│                                                              │
│  • The 4% Rule Explained (and when it doesn't apply)        │
│  • Federal Student Loan Playbook: IDR, PSLF, Refinancing   │
│  • 401K vs Roth IRA: What to Prioritize and When            │
│  • HYSA vs I-Bonds vs CDs: Where to Park Your Cash          │
│  • Building Your First Investment Portfolio                   │
│  • The Debt Avalanche vs Snowball: Which Is Right for You    │
│                                                              │
│  [Notify Me When It Launches]                                │
│  Email: [_______________] [Submit]                           │
│                                                              │
└────────────────────────────────────────────────────────────┘
```

---

### 6.11 Settings

**URL:** `/settings`
**Nav item:** ⚙️ Settings

**Layout:** Use Vuexy's **Account Settings** page template with tabs.

**Tabs:**

**Account:**
- Name, email, password change
- Delete account (with confirmation modal and data export option)

**Preferences:**
- Default withdrawal rate (4%, 5%, 6%)
- Inflation assumption
- Currency display (USD only for MVP, but build for extensibility)
- Dark mode toggle (Vuexy built-in)

**Notifications (Phase 2):**
- Monthly check-in reminders (email on/off)
- New recommendation alerts (email on/off)
- Milestone celebrations (on/off)

**Subscription (Phase 2):**
- Current plan display
- Upgrade/downgrade buttons
- "Manage Subscription" → Stripe Customer Portal
- Billing history

**Data & Privacy:**
- Export all data (JSON/CSV)
- "We never sell your data" statement
- Link to privacy policy

---

## 7. MODULE DEEP DIVES

### 7.1 The "Lever Toggle" Interaction (Action Plan)

This is the most important interactive element in the app. It must feel responsive and satisfying.

**How it works:**

1. When AI recommendations are generated, the PHP engine pre-calculates the timeline impact of each recommendation independently
2. These pre-calculated deltas are stored alongside the AI recommendation in the database
3. The frontend receives both the AI narrative AND the numerical impacts
4. When the user toggles a lever on/off, JavaScript updates the timeline display using the pre-calculated data — **no API call needed**
5. The sticky sidebar updates instantly with a smooth animation

**Implementation detail:**
```
// Each recommendation comes with:
{
  id: "rec_001",
  title: "Max 401K match",
  timeline_impact_months: -38,  // saves 38 months (pre-calculated by PHP)
  enabled: true  // default state
}

// Frontend calculation (JavaScript is OK here because it's just addition):
// base_months = 624 (52 years)
// enabled_savings = sum of all enabled levers' timeline_impact_months
// adjusted_months = base_months - enabled_savings
// This is simple arithmetic, not financial modeling — JS is fine
```

### 7.2 Monthly Check-In UX

**Design goal:** Under 60 seconds to complete.

**Flow:**
1. User clicks "Monthly Check-In" (from dashboard card, progress page, or email nudge)
2. Single form, all fields pre-filled with last month's values
3. Changed fields are highlighted
4. One-tap save
5. Celebration moment: "You reduced debt by $1,500 this month! Your freedom timeline moved up 2 months."
6. If significant change (>10% in any metric): "Your situation has changed significantly. Want us to refresh your Action Plan?" → triggers AI re-generation

### 7.3 Empty States

Every module must handle the case where no data exists yet:

| Module | Empty State Message | CTA |
|--------|-------------------|-----|
| Dashboard | "Welcome! Let's calculate your Freedom Number to get started." | "Start Calculator →" |
| My Finances — Debts | "No debts added yet. Lucky you — or haven't gotten to it?" | "Add Your First Debt" |
| My Finances — Investments | "No accounts added yet. Even $100 counts." | "Add Your First Account" |
| Action Plan | "Complete your financial profile to get personalized recommendations." | "Complete Profile →" |
| Progress | "Log your first check-in to start tracking your journey." | "Log First Check-In" |

**Tone for empty states:** Always encouraging, never judgmental. Light humor is OK.

---

## 8. NOTIFICATIONS & NUDGES

### In-App Notifications

**Location:** Bell icon in top navbar (Vuexy built-in component)

**Notification types:**
- "Your Action Plan is ready!" (after AI generation completes)
- "Time for your monthly check-in" (25+ days since last snapshot)
- "Milestone reached: You've paid off [debt name]!" (when a debt balance hits zero)
- "Your net worth just turned positive!" (first time net worth > 0)
- "You've reached 10% of your Freedom Number!" (milestone percentages: 10%, 25%, 50%, 75%, 90%)

### Email Notifications (Phase 2)

- Monthly check-in reminder (configurable day of month)
- Action plan ready
- Milestone celebrations

### Nudge Strategy

```
Day 1:   User registers → onboarding → action plan
Day 7:   In-app nudge: "How's it going? Any updates to log?"
Day 25:  In-app badge on Progress nav item
Day 30:  Dashboard card turns yellow: "Monthly check-in overdue"
Day 45:  Email: "We miss you! Your freedom number is waiting."
Day 90:  Email: "3 months since your last update. Life changes — let's recalculate."
```

---

## 9. MOBILE RESPONSIVENESS

### Breakpoints (Vuexy default)

- **Desktop:** 1200px+ (full sidebar, multi-column layouts)
- **Tablet:** 768px - 1199px (collapsed sidebar, 2-column where possible)
- **Mobile:** < 768px (no sidebar, single column, hamburger menu)

### Mobile-Specific Adjustments

- **Dashboard hero stats:** 2x2 grid instead of 4-in-a-row
- **Action Plan:** Single column — recommendation cards stack, timeline panel moves to top (sticky) or becomes a collapsible summary
- **Charts:** Full width, scrollable horizontally if needed
- **Monthly check-in:** Full screen form, large touch targets
- **Landing page:** Stacked sections, hero CTA is full-width button

---

## 10. TONE & COPY GUIDELINES

### Voice

FreedomStack speaks like a **financially literate friend who happens to have an MBA** — not a bank, not a guru, not a robot.

### Principles

1. **Honest but optimistic.** Never sugarcoat numbers, but always follow hard truths with a path forward.
2. **Specific, not vague.** "Increase your 401K contribution from 3% to 6%" not "Save more for retirement."
3. **Empathetic to debt.** Never shame debt. Many users have $50-150K in student loans. That's normal for the target audience.
4. **Anti-jargon where possible.** Explain terms on first use. "Safe withdrawal rate (the amount you can take out each year without running out)" not just "SWR."
5. **Celebratory at milestones.** Small wins matter enormously for long-term behavior. Celebrate every debt paid off, every percentage point gained.
6. **Never prescriptive as "advice."** Always frame as "based on your numbers, here's what the math suggests" — not "you should do X."

### Copy Examples

| Situation | ❌ Bad | ✅ Good |
|-----------|--------|---------|
| High debt load | "You have a lot of debt to pay off." | "Your debt-free date is April 2030. Here's the fastest path there." |
| Negative net worth | "Your net worth is negative." | "Your net worth is -$34,000. This is your starting line, not your finish line." |
| Missing employer match | "You're not maximizing your 401K." | "Your employer offers $3,000/year in free money through 401K matching. Right now you're capturing $1,800 of it." |
| Slow progress | "You're not saving enough." | "At your current rate, you're 2.9% of the way there. Every percentage point counts — and the first 10% is the hardest." |
| Achievement | "Goal met." | "You just crossed 10% of your Freedom Number. That took 14 months. The next 10% will be faster — compound growth is on your side now. 🎉" |

### Disclaimer (Required on Every Page)

Footer text on every page within the dashboard:
> "FreedomStack provides financial information for educational purposes only. This is not financial, investment, or tax advice. Consult a qualified professional before making financial decisions."

---

## APPENDIX: USER STATE MACHINE

```
[Anonymous]
    │
    ├── visits landing page
    ├── uses public calculator
    ├── sees results
    │
    ▼
[Registered, Onboarding Incomplete]
    │
    ├── completing onboarding steps (can skip any)
    │
    ▼
[Registered, Onboarding Complete, No Recommendations]
    │
    ├── AI job queued, waiting for results
    │
    ▼
[Active User, Has Recommendations]
    │
    ├── can access all modules
    ├── returns for monthly check-ins
    ├── refreshes recommendations on profile changes
    │
    ▼
[Active User, Subscribed] (Phase 2)
    │
    ├── premium features unlocked
    ├── priority AI model
    └── advanced scenarios
```

Each state determines what the user sees:
- **Anonymous:** Landing page + public calculator only
- **Onboarding incomplete:** Onboarding flow (can skip to dashboard, but nav shows "Complete Profile" prompt)
- **No recommendations:** Dashboard shows "Generating your plan..." or "Complete your profile to get recommendations"
- **Active:** Full dashboard experience
- **Subscribed:** Premium badge, unlocked features

---

*This document defines what to build. See `FREEDOMSTACK_CURSOR_INSTRUCTIONS.md` for how to build it.*