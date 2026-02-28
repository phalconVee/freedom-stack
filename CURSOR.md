# FreedomStack — Cursor Agent Build Instructions

> **App Name:** FreedomStack (working title)
> **Purpose:** AI-powered financial freedom calculator + personalized action plan generator
> **Stack:** Vue.js (Vuexy template) + Laravel API + PostgreSQL + OpenRouter LLM
> **Deployment:** Railway
> **Version:** MVP v1.0
>
> **⚠️ COMPANION DOCUMENT:** This file covers technical architecture and implementation.
> For the complete user journey, screen specifications, navigation structure, and UI/UX details,
> see **`FREEDOMSTACK_USER_JOURNEY.md`**. Both documents are required — this one tells you
> HOW to build it, that one tells you WHAT to build.

---

## TABLE OF CONTENTS

1. [Project Overview & Product Vision](#1-project-overview--product-vision)
2. [Architecture & Technical Stack](#2-architecture--technical-stack)
3. [Vuexy Template Integration](#3-vuexy-template-integration)
4. [Figma-to-Code Workflow](#4-figma-to-code-workflow)
5. [Database Setup (Local + Railway)](#5-database-setup-local--railway)
6. [OpenRouter AI Integration](#6-openrouter-ai-integration)
7. [Financial Calculation Engine](#7-financial-calculation-engine)
8. [Stripe Integration (Phase 2 — Planned)](#8-stripe-integration-phase-2--planned)
9. [Skills & MCP Servers to Install](#9-skills--mcp-servers-to-install)
10. [MVP Build Phases](#10-mvp-build-phases)
11. [File & Folder Structure](#11-file--folder-structure)
12. [Database Schema & Migrations](#12-database-schema--migrations)
13. [API Route Map](#13-api-route-map)
14. [Domain Rules & Constraints](#14-domain-rules--constraints)
15. [Testing Strategy](#15-testing-strategy)
16. [Deployment to Railway](#16-deployment-to-railway)
17. [Monetization Architecture](#17-monetization-architecture)

---

## 1. PROJECT OVERVIEW & PRODUCT VISION

### What FreedomStack Does

FreedomStack answers one question: **"How much money do I need to never worry about my monthly expenses again?"** — then tells you exactly how to get there.

It is NOT a generic budgeting app. It is NOT a retirement calculator. It is a **reverse-engineering tool** that:

1. Takes the user's monthly overhead and calculates their **"Freedom Number"** (the principal needed to passively cover expenses at various return/withdrawal rates)
2. Assesses their **current financial position** (income, savings, debts, investments)
3. Uses AI to generate a **personalized, prioritized action plan** showing which financial levers to pull and how each one impacts their timeline to financial freedom
4. Provides a **dashboard for tracking progress** over time with monthly manual updates

### Target User Persona

**"Marcus"** — 28, recent MBA grad, $85K salary, $60K in student loans, $8K in savings, $3,200/month overhead. Wants to know what financial independence looks like and what specific steps to take next.

The initial audience is **MBA graduates and young professionals with student debt** pursuing financial independence. Build for this persona first. Do not try to serve everyone.

### Core User Journey

```
Landing Page → Guided Expense Intake (form wizard)
→ "Freedom Number" Results (gut-punch moment — big number, clear visual)
→ Financial Profile Intake (income, debts, savings, investments)
→ "The Gap" Analysis (how far away, at current trajectory)
→ AI-Powered Recommendations (personalized levers with timeline impact)
→ Dashboard (return visits — manual updates, progress tracking, new AI suggestions)
```

---

## 2. ARCHITECTURE & TECHNICAL STACK

### System Architecture

```
┌─────────────────────────┐         ┌─────────────────────────┐
│   Vue.js SPA (Vuexy)    │──HTTP──▶│    Laravel API           │
│   - Vue 3 + Composition │  JSON   │    - Sanctum Auth        │
│   - Pinia State          │         │    - Eloquent ORM        │
│   - Vue Router           │         │    - Service Classes     │
│   - ApexCharts           │         │    - Queue Workers       │
│   - Vuexy Components     │         │    - JSON API Resources  │
└─────────────────────────┘         └────────┬────────────────┘
                                             │
                              ┌──────────────┼──────────────┐
                              ▼              ▼              ▼
                    ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
                    │  PostgreSQL  │ │  OpenRouter   │ │  Stripe      │
                    │  (Railway)   │ │  (AI/LLM)    │ │  (Phase 2)   │
                    └──────────────┘ └──────────────┘ └──────────────┘
```

### Stack Decisions (Do Not Override)

| Layer | Technology | Why |
|-------|-----------|-----|
| Frontend | Vue 3 (Vuexy template) | Pre-built dashboard, charts, form wizards, auth pages |
| Backend | Laravel 11+ | Full-featured API framework, Sanctum auth, queues, Eloquent |
| Database | PostgreSQL 17 | Railway-native, reliable, handles financial data well |
| AI/LLM | OpenRouter → DeepSeek V3.2 (MVP) | $0.001/recommendation, OpenAI-compatible API |
| Payments | Stripe (Phase 2) | Industry standard, Laravel Cashier integration |
| Deployment | Railway | $5/mo Hobby plan, Laravel + Postgres in one platform |
| Dev Tool | Cursor | AI-powered IDE, MCP server support, Skills integration |

### What We Are NOT Using (And Why)

- **Supabase**: Not needed. Railway provides PostgreSQL. Laravel provides auth, ORM, and API. Supabase would duplicate all of these with worse Laravel integration.
- **Valyu**: Not needed for MVP. We don't need real-time market data. The LLM generates strategy recommendations from user-entered financial data.
- **Open-ended AI chatbot**: The entry point is structured forms, NOT a prompt box. AI powers the OUTPUT (recommendations), not the INPUT.

---

## 3. VUEXY TEMPLATE INTEGRATION

### CRITICAL: Read the Template Before Writing Code

The Vuexy Vue + Laravel template is located at:
```
./vuexy-vue-laravel-template/
```

**Before building ANY frontend component, the agent MUST:**

1. Scan the template's directory structure to understand available components
2. Check if a similar component already exists in Vuexy before creating a new one
3. Follow Vuexy's design system (colors, spacing, typography, component patterns)
4. Use Vuexy's pre-built components wherever possible

### Template Scanning Protocol

When starting any frontend task, run these steps:

```bash
# 1. Understand the template structure
find ./vuexy-vue-laravel-template -type f -name "*.vue" | head -100

# 2. Check for pre-built components relevant to the task
# Examples: form wizards, charts, cards, tables, auth pages
find ./vuexy-vue-laravel-template -type f -name "*.vue" | grep -i "wizard\|stepper\|chart\|card\|dashboard\|auth\|login\|register"

# 3. Check the design system / theme configuration
find ./vuexy-vue-laravel-template -type f -name "*.ts" -o -name "*.js" | grep -i "theme\|config\|color\|style"

# 4. Check existing layouts
find ./vuexy-vue-laravel-template -type f -name "*.vue" | grep -i "layout"
```

### Components to Reuse from Vuexy

| FreedomStack Feature | Vuexy Component to Use |
|---------------------|----------------------|
| Expense intake form | **Form Wizard / Stepper** component |
| Freedom Number display | **Statistics Cards** (big number + icon) |
| Gap analysis visualization | **ApexCharts** (bar/radial charts) |
| AI recommendations | **Analytics Cards** with action items |
| Progress dashboard | **Dashboard layout** with chart widgets |
| Login / Register | **Auth pages** (pre-built) |
| Settings / Profile | **Account Settings** page template |
| Monthly update form | **Form elements** with pre-filled defaults |
| Data tables (debts, expenses) | **DataTable** component |

### Design System Rules

- **DO** use Vuexy's color palette, typography scale, and spacing tokens
- **DO** use Vuexy's built-in dark mode support
- **DO** use the template's icon library (Iconify / Material Design Icons)
- **DO NOT** introduce new CSS frameworks (no Bootstrap, no custom Tailwind config)
- **DO NOT** override Vuexy's base theme unless explicitly required
- **DO NOT** create custom components when a Vuexy equivalent exists
- **DO** use Vuexy's responsive grid and layout system
- **DO** follow the template's folder naming conventions

### Vuexy Laravel Integration Notes

The Vuexy template includes a Laravel integration where:
- The Vue SPA is served as the frontend
- Laravel serves as a pure API backend
- Axios is pre-configured with base URL pointing to Laravel API routes
- JWT/Sanctum token management is handled in localStorage
- CORS is pre-configured between Vue dev server and Laravel

**Follow Vuexy's documented folder structure for the Laravel integration.** Do not restructure.

---

## 4. FIGMA-TO-CODE WORKFLOW

### MCP Server Setup

FreedomStack uses the **Figma MCP Server** to bring design context directly into Cursor. This enables the agent to reference Figma frames when building components.

#### Option A: Figma Remote MCP Server (Recommended — No Desktop App Required)

Add to `~/.cursor/mcp.json`:
```json
{
  "mcpServers": {
    "figma": {
      "url": "https://mcp.figma.com/mcp"
    }
  }
}
```

After adding, go to Cursor Settings → MCP → click **Connect** next to Figma → authenticate with your Figma account.

#### Option B: Figma Desktop MCP Server (If Using Desktop App)

1. Open Figma Desktop app
2. Open a design file
3. Toggle to Dev Mode (Shift+D)
4. In the inspect panel, click "Enable desktop MCP server"
5. Add to `~/.cursor/mcp.json`:

```json
{
  "mcpServers": {
    "figma-desktop": {
      "url": "http://127.0.0.1:3845/mcp"
    }
  }
}
```

#### Option C: Framelink MCP (Third-Party — Most Cursor-Optimized)

```json
{
  "mcpServers": {
    "Framelink MCP for Figma": {
      "command": "npx",
      "args": ["-y", "figma-developer-mcp", "--figma-api-key=YOUR_FIGMA_API_KEY", "--stdio"]
    }
  }
}
```

Generate a Figma API key at: https://www.figma.com/developers/api#access-tokens

### Figma-to-Code Agent Protocol

When building a component from a Figma design:

1. **Receive Figma link or frame selection** from the developer
2. **Use the MCP tools** to fetch layout, spacing, typography, and color data from the Figma frame
3. **Map Figma tokens to Vuexy's design system** — do NOT create custom CSS if Vuexy has an equivalent token
4. **Generate a Vue component** using Vuexy's component library + the Figma layout data
5. **Validate** that the output matches the Figma design visually

### Figma Agent Skill (Install in Project)

Create `.cursor/skills/figma-to-vuexy/SKILL.md`:

```markdown
---
name: figma-to-vuexy
description: Convert Figma designs to Vue components using Vuexy template components and design system. Trigger when user provides a Figma link or asks to implement a design.
---

# Figma to Vuexy Component Conversion

## Steps

### Step 1: Fetch Design Data
Use the Figma MCP tools to get the frame's layout, colors, typography, and spacing.

### Step 2: Map to Vuexy Components
Before creating ANY custom HTML/CSS:
- Check if Vuexy has a matching component (card, chart, form element, etc.)
- Use Vuexy's color variables instead of hardcoded hex values
- Use Vuexy's spacing scale instead of arbitrary pixel values
- Use Vuexy's typography classes instead of custom font styles

### Step 3: Generate Vue Component
Create a .vue file using Vue 3 Composition API (<script setup>) that:
- Uses Vuexy components as building blocks
- Follows the project's folder structure
- Includes proper TypeScript types
- Is responsive using Vuexy's grid system

### Step 4: Validate
Compare the generated component against the Figma frame for visual accuracy.
```

---

## 5. DATABASE SETUP (LOCAL + RAILWAY)

### Local Development: PostgreSQL with Docker

For local development, use Docker to run PostgreSQL:

```bash
# Create a docker-compose.yml in project root (if not using Laravel Sail)
docker-compose up -d postgres
```

**docker-compose.yml (database service only):**
```yaml
version: '3.8'
services:
  postgres:
    image: postgres:17
    container_name: freedomstack_db
    environment:
      POSTGRES_DB: freedomstack
      POSTGRES_USER: freedomstack
      POSTGRES_PASSWORD: secret
    ports:
      - "5432:5432"
    volumes:
      - pgdata:/var/lib/postgresql/data

volumes:
  pgdata:
```

**Laravel .env (local):**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=freedomstack
DB_USERNAME=freedomstack
DB_PASSWORD=secret
```

### Alternative: Laravel Sail (If Preferred)

If the project uses Laravel Sail, PostgreSQL is included:
```bash
./vendor/bin/sail up -d
```

### Railway PostgreSQL (Production)

Railway provisions PostgreSQL 17 with a single click. Configuration:

1. In Railway dashboard → New Project → Add PostgreSQL
2. Railway provides a `DATABASE_URL` environment variable
3. In the Laravel service, set:

```env
DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}
```

**Railway Postgres Features:**
- SSL encryption by default
- Scheduled backups (daily on Hobby plan)
- Private networking between services (zero-latency app-to-DB communication)
- 5 GB storage on Hobby plan (sufficient for MVP)
- Connection string auto-injected via Railway's reference variables

**Important:** Railway's filesystem is ephemeral. Set these in production:
```env
LOG_CHANNEL=stderr
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

Run `php artisan session:table` and `php artisan cache:table` to generate the required migration files if using database drivers for session and cache.

---

## 6. OPENROUTER AI INTEGRATION

### Setup

Install the OpenAI PHP Laravel package (works with OpenRouter since it's OpenAI-compatible):

```bash
composer require openai-php/laravel
```

**Environment variables:**
```env
OPENAI_API_KEY=sk-or-v1-your-openrouter-key
OPENAI_BASE_URL=https://openrouter.ai/api/v1
OPENROUTER_DEFAULT_MODEL=deepseek/deepseek-v3.2
```

### Model Strategy

| Environment | Model | Cost | Use |
|------------|-------|------|-----|
| Development | `deepseek/deepseek-r1:free` | $0 | Testing (50 req/day) |
| MVP Production | `deepseek/deepseek-v3.2` | ~$0.001/req | Primary model |
| Premium (Future) | `anthropic/claude-sonnet-4` | ~$0.01-0.03/req | Higher quality tier |
| Fallback | `openai/gpt-4.1-mini` | ~$0.005/req | If primary unavailable |

### AI Service Architecture

```
app/Services/AIRecommendationService.php
├── generateRecommendations(FinancialProfile $profile): array
├── buildSystemPrompt(): string
├── buildUserPrompt(array $sanitizedData): string
├── sanitizeFinancialData(FinancialProfile $profile): array  // STRIPS ALL PII
├── parseAndValidateResponse(string $response): array
└── validateAIArithmetic(array $aiOutput, array $phpCalculations): array
```

### CRITICAL RULES FOR AI INTEGRATION

```
╔══════════════════════════════════════════════════════════════════╗
║  RULE 1: THE LLM NEVER DOES MATH                               ║
║                                                                  ║
║  All financial calculations (freedom number, debt payoff,        ║
║  compound interest, timeline projections) are computed in        ║
║  deterministic PHP service classes using bcmath.                 ║
║                                                                  ║
║  The LLM receives the RESULTS of these calculations and         ║
║  generates STRATEGY NARRATIVES and PRIORITIZATION.               ║
║                                                                  ║
║  If the LLM returns any numbers, they MUST be validated          ║
║  against the PHP calculation engine before being displayed.      ║
║                                                                  ║
║  RULE 2: NO PII SENT TO LLM                                     ║
║                                                                  ║
║  Before sending data to OpenRouter, strip ALL personally         ║
║  identifiable information. Replace names with "User", bank       ║
║  names with "Account 1", employer with "Employer".               ║
║  Send only: dollar amounts, interest rates, percentages,         ║
║  time periods, and categorical data (filing status, etc).        ║
║                                                                  ║
║  RULE 3: CACHE AGGRESSIVELY                                      ║
║                                                                  ║
║  Hash the financial profile (bucketed income + debt level)       ║
║  and cache AI responses for 24 hours. Same financial bracket     ║
║  = cache hit. Max 10 AI calls per user per day.                  ║
║                                                                  ║
║  RULE 4: ASYNC PROCESSING                                        ║
║                                                                  ║
║  AI calls go through Laravel Queue (database driver).            ║
║  User submits profile → job dispatched → AI called →             ║
║  result stored → event broadcast → frontend notified.            ║
║  Never block the HTTP request waiting for AI response.           ║
╚══════════════════════════════════════════════════════════════════╝
```

### Prompt Engineering

**System Prompt Template:**
```
You are a certified financial planning assistant. You receive a user's
anonymized financial profile along with pre-calculated results from
our financial engine.

YOUR ROLE: Generate a prioritized action plan that explains WHY each
action matters and HOW it impacts the user's timeline to financial freedom.

STRICT RULES:
- Do NOT perform any mathematical calculations. All numbers are provided
  to you pre-calculated. Reference them directly.
- Respond ONLY with valid JSON matching the schema below.
- All dollar amounts must reference the pre-calculated figures provided.
- All timeline estimates must reference the pre-calculated months/years.
- Use the avalanche method by default for debt payoff prioritization.
- Prioritize actions by highest impact on timeline reduction.
- Include a "confidence" field (high/medium/low) for each recommendation.
- Maximum 5 recommendations, ranked by impact.

RESPONSE SCHEMA:
{
  "summary": "string — 2-3 sentence overview of the user's situation",
  "recommendations": [
    {
      "id": "string",
      "title": "string — short action title",
      "description": "string — why this matters and what to do",
      "category": "debt_payoff | savings | retirement | income | expense_reduction",
      "priority": 1-5,
      "timeline_impact_months": number (from pre-calculated data),
      "confidence": "high | medium | low",
      "specific_actions": ["string array of concrete next steps"]
    }
  ],
  "debt_strategy": {
    "method": "avalanche | snowball",
    "order": ["Debt 1", "Debt 2"],
    "reasoning": "string"
  },
  "key_insight": "string — one powerful takeaway for the user"
}
```

**User Prompt Template (Sanitized):**
```
FINANCIAL PROFILE:
- Monthly net income: $X
- Monthly expenses: $X (essential: $X, discretionary: $X)
- Filing status: [single/married/etc]
- Risk tolerance: [conservative/moderate/aggressive]

DEBTS:
- Debt 1: $X balance, X% interest, $X minimum payment, [type]
- Debt 2: ...

ASSETS:
- Savings: $X
- Emergency fund: $X
- 401K: $X (employer match: X% up to $X)
- IRA: $X
- Brokerage: $X
- HYSA: $X

PRE-CALCULATED RESULTS (from our financial engine — reference these directly):
- Freedom Number (4% SWR): $X
- Freedom Number (5% SWR): $X
- Freedom Number (6% SWR): $X
- Current gap: $X
- Years to freedom at current savings rate: X years
- Debt-free date (avalanche): MM/YYYY
- Debt-free date (snowball): MM/YYYY
- Total interest saved (avalanche vs snowball): $X
- Monthly cash freed after debt payoff: $X
- Employer match optimization: $X/year left on table (if any)
- Recommended emergency fund target: $X

Generate a personalized action plan based on this profile.
```

### Queue Job Implementation

```php
// app/Jobs/GenerateRecommendations.php

// 1. Receive user_id and financial_profile_id
// 2. Load profile, run PHP calculations first
// 3. Sanitize data (strip PII)
// 4. Construct prompt with pre-calculated results
// 5. Call OpenRouter via openai-php/laravel
// 6. Parse JSON response, validate structure
// 7. Cross-check any AI-returned numbers against PHP calculations
// 8. Store in ai_recommendations table
// 9. Broadcast event to notify frontend
// 10. Handle failures gracefully (retry 3x, then mark as failed)
```

---

## 7. FINANCIAL CALCULATION ENGINE

### ABSOLUTE RULE: All Math in PHP, All Math Deterministic

```
╔══════════════════════════════════════════════════════════════╗
║  NEVER let the LLM calculate numbers.                        ║
║  NEVER use PHP's native float arithmetic for money.          ║
║  ALWAYS use bcmath or brick/money for financial operations.  ║
║  ALWAYS store monetary values as integers (cents) in the DB. ║
╚══════════════════════════════════════════════════════════════╝
```

### Required PHP Package

```bash
composer require brick/money
```

### Service Classes

#### FreedomNumberCalculator.php

```php
// Located at: app/Services/FreedomNumberCalculator.php
//
// Core formula: Freedom Number = Annual Expenses / Withdrawal Rate
//
// Methods:
// - calculate(monthlyExpenses, withdrawalRate): returns freedom number
// - calculateRange(monthlyExpenses): returns array of freedom numbers at 3%, 4%, 5%, 6% rates
// - adjustForInflation(currentExpenses, inflationRate, years): returns future expenses
// - calculateWithVariableWithdrawal(expenses, strategy): Guyton-Klinger guardrails
//
// IMPORTANT: Use bcmath for all division/multiplication
// Example: bcdiv(bcmul($annualExpenses, '100', 2), $withdrawalRatePercent, 2)
```

#### DebtPayoffOptimizer.php

```php
// Located at: app/Services/DebtPayoffOptimizer.php
//
// Implements both avalanche (highest interest first) and snowball (lowest balance first)
//
// Methods:
// - calculateAvalanche(array $debts, extraPayment): returns payoff schedule
// - calculateSnowball(array $debts, extraPayment): returns payoff schedule
// - compareMethods(array $debts, extraPayment): returns comparison (total interest, months, savings)
// - calculateOpportunityCost(debtPayment, investmentReturn, years): shows invest-vs-payoff tradeoff
//
// Each method returns:
// - Total months to debt-free
// - Total interest paid
// - Month-by-month payoff schedule
// - Date of final payment
// - Monthly cash freed after each debt is paid off
```

#### TimelineProjectionService.php

```php
// Located at: app/Services/TimelineProjectionService.php
//
// Core formula: FV = PV × (1 + r)^n + PMT × [((1 + r)^n - 1) / r]
//
// Methods:
// - projectTimeline(currentSavings, monthlyContribution, returnRate, targetAmount): returns months
// - projectWithLevers(profile, levers[]): returns timeline for each lever combination
// - calculateSavingsRate(income, expenses, debtPayments): returns percentage
// - runMonteCarlo(profile, simulations=1000): returns probability distribution (Phase 2)
//
// ALL arithmetic uses bcmath with 10 decimal precision
// Interest compounding is monthly: monthlyRate = bcdiv($annualRate, '12', 10)
```

#### RetirementOptimizer.php

```php
// Located at: app/Services/RetirementOptimizer.php
//
// Methods:
// - calculateEmployerMatchGap(currentContribution, matchPercent, matchLimit, salary): returns money left on table
// - optimizeAccountOrder(income, filingStatus, accounts[]): returns contribution priority order
//   (typically: 401K up to match → HSA → Roth IRA → 401K max → taxable brokerage)
// - calculateTaxSavings(traditionalContribution, taxBracket): returns annual tax savings
// - calculateRothConversionBenefit(amount, currentBracket, expectedRetirementBracket): returns analysis
```

### Financial Data Precision Rules

1. **Store all monetary values as integers (cents)** in the database. A balance of $1,234.56 is stored as `123456`.
2. **Use `bcmath` with scale of 10** for all intermediate calculations.
3. **Round to 2 decimal places** only at the display layer (in API responses).
4. **Never use `float` or `double`** for financial values — PHP floating point introduces compounding rounding errors over 20-30 year projections.
5. **Use `brick/money`** for currency formatting and conversion.

---

## 8. STRIPE INTEGRATION (PHASE 2 — PLANNED)

### Current Status: NOT YET IMPLEMENTED

Stripe is planned for Phase 2 when the freemium monetization model is activated. **Do not build Stripe integration during MVP Phase 1.** Instead, prepare the architecture so it's easy to add later.

### Phase 2 Implementation Plan

#### Package
```bash
composer require laravel/cashier
```

#### Pricing Model
```
Free Tier (no account required):
- Basic freedom number calculator
- Student loan payoff estimator
- 1 AI recommendation summary

Premium Tier ($8-10/month OR $79-89/year OR $399 lifetime):
- Unlimited AI recommendation refreshes
- Detailed personalized action plans
- Advanced scenario modeling
- Debt optimization engine (avalanche vs snowball comparison)
- Tax-advantaged account optimization
- Progress tracking dashboard
- Priority AI model (Claude Sonnet instead of DeepSeek)
```

#### Database Preparation (Add in Phase 1 Migrations)

Even though Stripe isn't active yet, include these columns in the users table migration now:

```php
// In users migration — add these as nullable columns
$table->string('stripe_id')->nullable()->index();
$table->string('pm_type')->nullable();
$table->string('pm_last_four', 4)->nullable();
$table->timestamp('trial_ends_at')->nullable();
```

And create the subscriptions + subscription_items tables:
```bash
php artisan cashier:install
```

#### Architecture Notes for Phase 2

- Use **Stripe Checkout** (hosted payment page) — NOT custom payment forms. This avoids PCI compliance burden entirely.
- Use **Stripe Webhooks** for subscription lifecycle events (created, updated, canceled, payment failed)
- Implement a `SubscriptionMiddleware` that checks `$user->subscribed('premium')` for gated routes
- Use Stripe's **Customer Portal** for self-service subscription management (cancel, update payment, view invoices)
- Support **lifetime purchases** via a one-time Stripe Checkout session that sets a `lifetime_access` boolean on the user record

#### Stripe MCP Server (For Development)

When Phase 2 begins, add the Stripe MCP server to Cursor:

```json
// Add to ~/.cursor/mcp.json
{
  "mcpServers": {
    "stripe": {
      "command": "npx",
      "args": ["-y", "@stripe/mcp", "--tools=all", "--api-key=sk_test_YOUR_KEY"]
    }
  }
}
```

This lets the Cursor agent interact with the Stripe API directly during development — create test products, prices, and subscriptions without leaving the editor.

#### Stripe Agent Skill (Install When Phase 2 Begins)

```bash
npx skills add wshobson/agents --skill stripe-integration
```

This skill teaches the agent PCI-compliant payment flows, webhook handling, and subscription management patterns.

---

## 9. SKILLS & MCP SERVERS TO INSTALL

### Agent Skills (Install Before Starting Development)

Run these in the project root:

```bash
# Vue 3 development patterns (Composition API, Pinia, Vue Router)
npx skills add vuejs-ai/skills

# Laravel development patterns (TDD, migrations, Eloquent, queues, policies)
npx skills add jpcaparas/superpowers-laravel

# Laravel official patterns
npx skills add laravel/boost
```

### MCP Servers (Add to ~/.cursor/mcp.json)

```json
{
  "mcpServers": {
    "figma": {
      "url": "https://mcp.figma.com/mcp"
    }
  }
}
```

**Add Stripe MCP only when Phase 2 begins** (see Section 8).

### Custom Project Skill (Create This File)

Create `.cursor/skills/freedomstack/SKILL.md` in the project root:

```markdown
---
name: freedomstack
description: >-
  FreedomStack app conventions, architecture rules, and financial domain
  constraints. Use for ALL development tasks in this project. Triggers on
  any code generation, component creation, API endpoint, or database change.
---

# FreedomStack Project Conventions

## Architecture
- Vue 3 SPA (Vuexy template) communicating with Laravel API via JSON
- All frontend components MUST use Vuexy components first, custom only if no match
- All financial math in PHP service classes using bcmath — NEVER in LLM, NEVER in JavaScript
- AI calls are async via Laravel Queue — never block HTTP requests
- No PII sent to OpenRouter — sanitize all data before AI calls

## Code Style
- Vue: Composition API with <script setup lang="ts">
- Laravel: strict typing, service classes for business logic, thin controllers
- Database: monetary values stored as integers (cents)
- API: always return JSON API Resources, never raw Eloquent models

## File Naming
- Vue components: PascalCase (FreedomCalculator.vue)
- Laravel controllers: PascalCase + Controller suffix (ExpenseController.php)
- Laravel services: PascalCase + descriptive suffix (FreedomNumberCalculator.php)
- Migrations: Laravel default (timestamp_create_tablename_table.php)
- Tests: PascalCase + Test suffix (FreedomNumberCalculatorTest.php)

## Domain Rules
- The "Freedom Number" = Annual Expenses / Withdrawal Rate
- Default withdrawal rate: 4% (Trinity Study safe withdrawal rate)
- Always show a RANGE of freedom numbers (3%, 4%, 5%, 6%) — never just one
- Debt payoff default: avalanche method. Always show both avalanche and snowball.
- Emergency fund target: 6 months of essential expenses
- 401K optimization: always check employer match gap first
- Account contribution order: 401K to match → HSA → Roth IRA → 401K max → taxable
```

---

## 10. MVP BUILD PHASES

### Phase 1: Foundation (Week 1–2)

**Goal:** Running app with auth, basic database, deployed to Railway.

- [ ] Set up project: clone Vuexy Vue+Laravel template, initialize git repo
- [ ] Configure PostgreSQL locally via Docker
- [ ] Run Vuexy's Laravel setup (migrations, seeding, auth scaffolding)
- [ ] Configure Sanctum SPA authentication
- [ ] Create database migrations for: `financial_profiles`, `expenses`, `debts`, `investment_accounts`
- [ ] Set up Railway project: Laravel service + PostgreSQL service
- [ ] Configure GitHub → Railway auto-deploy pipeline
- [ ] Install Skills packages for Cursor
- [ ] Set up Figma MCP connection

### Phase 2: Freedom Number Calculator (Week 2–3)

**Goal:** User can input expenses and see their Freedom Number.

- [ ] Build guided expense intake using Vuexy's **Form Wizard/Stepper** component
- [ ] Expense categories: housing, transportation, food, insurance, healthcare, utilities, debt payments, personal, entertainment, education, other
- [ ] Implement `FreedomNumberCalculator` PHP service (bcmath)
- [ ] Create API endpoint: `POST /api/calculator/freedom-number`
- [ ] Build results dashboard using Vuexy's **Statistics Cards** + **ApexCharts**
- [ ] Display freedom number at 4%, 5%, 6% withdrawal rates
- [ ] Display "years to freedom" at current savings rate (if provided)
- [ ] Make it shareable (generate a unique result URL)

### Phase 3: AI Recommendation Engine (Week 4–5)

**Goal:** User gets personalized AI-powered action plan.

- [ ] Build financial profile intake form (income, debts, savings, investments)
- [ ] Implement `DebtPayoffOptimizer` PHP service
- [ ] Implement `TimelineProjectionService` PHP service
- [ ] Implement `RetirementOptimizer` PHP service
- [ ] Build `AIRecommendationService` with OpenRouter integration
- [ ] Implement async queue job for AI calls
- [ ] Build PII sanitization layer
- [ ] Create recommendation display UI with Vuexy's **Analytics Cards**
- [ ] Add "lever toggling" — user can enable/disable recommendations and see timeline impact
- [ ] Implement recommendation caching (24-hour TTL, bucketed by financial profile hash)
- [ ] Rate limit: 10 AI calls per user per day

### Phase 4: Dashboard + Progress Tracking (Week 6–8)

**Goal:** Users can return, update their numbers, and track progress.

- [ ] Build progress dashboard with Vuexy's **Dashboard** layout
- [ ] Implement `progress_snapshots` table and monthly snapshot creation
- [ ] Build monthly update form (pre-filled with last values, update only what changed)
- [ ] Net worth over time chart (ApexCharts line/area chart)
- [ ] Freedom percentage achieved (radial progress chart)
- [ ] Debt reduction visualization
- [ ] AI re-evaluation on significant changes (>10% change in any major metric)
- [ ] Monthly nudge notification system (email or in-app)
- [ ] Polish, test with 5-10 users, launch beta

### Phase 5: Monetization (Post-MVP)

- [ ] Integrate Stripe via Laravel Cashier
- [ ] Implement free/premium tier gating
- [ ] Build Stripe Checkout flow
- [ ] Configure webhooks for subscription lifecycle
- [ ] Add lifetime purchase option
- [ ] Set up Stripe Customer Portal for self-service management

---

## 11. FILE & FOLDER STRUCTURE

```
freedomstack/
├── .cursor/
│   ├── mcp.json                          # MCP server configs
│   └── skills/
│       └── freedomstack/
│           └── SKILL.md                  # Project-specific agent skill
│
├── vuexy-vue-laravel-template/           # READ-ONLY REFERENCE — do not modify directly
│
├── app/                                   # Laravel application
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── FinancialProfileController.php
│   │   │   ├── ExpenseController.php
│   │   │   ├── DebtController.php
│   │   │   ├── InvestmentAccountController.php
│   │   │   ├── FreedomCalculatorController.php
│   │   │   ├── RecommendationController.php
│   │   │   └── ProgressController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureProfileComplete.php
│   │   │   └── SubscriptionGate.php      # Phase 2
│   │   └── Resources/
│   │       ├── FinancialProfileResource.php
│   │       ├── ExpenseResource.php
│   │       ├── DebtResource.php
│   │       ├── FreedomCalculationResource.php
│   │       ├── RecommendationResource.php
│   │       └── ProgressSnapshotResource.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── FinancialProfile.php
│   │   ├── Expense.php
│   │   ├── Debt.php
│   │   ├── InvestmentAccount.php
│   │   ├── FreedomCalculation.php
│   │   ├── AIRecommendation.php
│   │   └── ProgressSnapshot.php
│   │
│   ├── Services/
│   │   ├── FreedomNumberCalculator.php
│   │   ├── DebtPayoffOptimizer.php
│   │   ├── TimelineProjectionService.php
│   │   ├── RetirementOptimizer.php
│   │   └── AIRecommendationService.php
│   │
│   ├── Jobs/
│   │   └── GenerateRecommendations.php
│   │
│   ├── Events/
│   │   └── RecommendationsGenerated.php
│   │
│   └── Data/
│       └── FinancialDataSanitizer.php
│
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   ├── xxxx_create_financial_profiles_table.php
│   │   ├── xxxx_create_expenses_table.php
│   │   ├── xxxx_create_debts_table.php
│   │   ├── xxxx_create_investment_accounts_table.php
│   │   ├── xxxx_create_freedom_calculations_table.php
│   │   ├── xxxx_create_ai_recommendations_table.php
│   │   └── xxxx_create_progress_snapshots_table.php
│   └── factories/
│       └── (one factory per model for testing)
│
├── routes/
│   └── api.php                            # All API routes
│
├── resources/js/                          # Vue SPA (from Vuexy)
│   ├── views/
│   │   ├── calculator/
│   │   │   ├── ExpenseIntake.vue          # Form wizard
│   │   │   └── FreedomNumberResults.vue   # Results dashboard
│   │   ├── profile/
│   │   │   ├── FinancialProfile.vue       # Income, debts, assets intake
│   │   │   └── GapAnalysis.vue            # "The Gap" visualization
│   │   ├── recommendations/
│   │   │   ├── ActionPlan.vue             # AI recommendations display
│   │   │   └── LeverToggle.vue            # Interactive timeline adjustment
│   │   ├── dashboard/
│   │   │   ├── ProgressDashboard.vue      # Main return-visit dashboard
│   │   │   └── MonthlyUpdate.vue          # Quick update form
│   │   └── auth/                          # From Vuexy (login, register, etc)
│   ├── stores/
│   │   ├── calculator.ts                  # Pinia store for calculator state
│   │   ├── profile.ts                     # Financial profile state
│   │   ├── recommendations.ts             # AI recommendations state
│   │   └── progress.ts                    # Progress tracking state
│   └── composables/
│       ├── useFinancialCalculations.ts    # Frontend display formatting only
│       └── useRecommendations.ts          # Polling/WebSocket for async results
│
├── tests/
│   ├── Feature/
│   │   ├── FreedomCalculatorTest.php
│   │   ├── DebtPayoffTest.php
│   │   ├── RecommendationFlowTest.php
│   │   └── ProgressTrackingTest.php
│   └── Unit/
│       ├── FreedomNumberCalculatorTest.php
│       ├── DebtPayoffOptimizerTest.php
│       ├── TimelineProjectionServiceTest.php
│       └── RetirementOptimizerTest.php
│
├── docker-compose.yml                     # Local PostgreSQL
├── railway.json                           # Railway deployment config
├── Procfile                               # Railway process types
└── .env.example
```

---

## 12. DATABASE SCHEMA & MIGRATIONS

### Users Table (extends Laravel default)

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->boolean('onboarding_completed')->default(false);
    // Stripe (Phase 2 — add now as nullable)
    $table->string('stripe_id')->nullable()->index();
    $table->string('pm_type')->nullable();
    $table->string('pm_last_four', 4)->nullable();
    $table->timestamp('trial_ends_at')->nullable();
    $table->boolean('lifetime_access')->default(false);
    $table->rememberToken();
    $table->timestamps();
});
```

### Financial Profiles

```php
Schema::create('financial_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->bigInteger('monthly_gross_income');        // stored in cents
    $table->bigInteger('monthly_net_income');           // stored in cents
    $table->bigInteger('monthly_expenses_total');       // stored in cents
    $table->enum('filing_status', ['single', 'married_joint', 'married_separate', 'head_of_household']);
    $table->string('state_of_residence', 2)->nullable();
    $table->integer('target_fire_age')->nullable();
    $table->integer('current_age')->nullable();
    $table->enum('risk_tolerance', ['conservative', 'moderate', 'aggressive'])->default('moderate');
    $table->timestamps();

    $table->index('user_id');
});
```

### Expenses

```php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('category', [
        'housing', 'transportation', 'food', 'insurance', 'healthcare',
        'utilities', 'debt_payments', 'personal', 'entertainment',
        'education', 'savings', 'other'
    ]);
    $table->string('name');
    $table->bigInteger('monthly_amount');               // stored in cents
    $table->boolean('is_essential')->default(true);
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index('user_id');
});
```

### Debts

```php
Schema::create('debts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('type', [
        'student_loan', 'credit_card', 'auto_loan', 'mortgage',
        'personal_loan', 'medical', 'other'
    ]);
    $table->string('name');
    $table->bigInteger('balance');                      // stored in cents
    $table->decimal('interest_rate', 5, 2);             // e.g., 6.50 for 6.5%
    $table->bigInteger('minimum_payment');               // stored in cents
    $table->bigInteger('original_balance')->nullable();  // stored in cents
    $table->integer('loan_term_months')->nullable();
    $table->boolean('is_federal_student_loan')->default(false);
    $table->string('repayment_plan')->nullable();        // IDR, standard, etc.
    $table->timestamps();

    $table->index('user_id');
});
```

### Investment Accounts

```php
Schema::create('investment_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('type', [
        '401k', '403b', 'traditional_ira', 'roth_ira',
        'hsa', 'brokerage', 'hysa', 'savings', 'other'
    ]);
    $table->string('name');
    $table->bigInteger('balance');                       // stored in cents
    $table->bigInteger('monthly_contribution')->default(0); // stored in cents
    $table->decimal('employer_match_pct', 5, 2)->nullable();
    $table->bigInteger('employer_match_limit')->nullable(); // stored in cents
    $table->decimal('estimated_annual_return', 5, 2)->default(7.00);
    $table->timestamps();

    $table->index('user_id');
});
```

### Freedom Calculations

```php
Schema::create('freedom_calculations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->bigInteger('monthly_expenses_used');         // stored in cents
    $table->decimal('withdrawal_rate', 5, 2);
    $table->decimal('expected_return_rate', 5, 2);
    $table->decimal('inflation_rate', 5, 2)->default(3.00);
    $table->bigInteger('freedom_number');                // stored in cents
    $table->decimal('years_to_freedom', 6, 2)->nullable();
    $table->decimal('monthly_savings_rate', 5, 2)->nullable();
    $table->json('assumptions')->nullable();
    $table->timestamps();

    $table->index('user_id');
});
```

### AI Recommendations

```php
Schema::create('ai_recommendations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->json('financial_profile_snapshot');
    $table->string('model_used');
    $table->string('prompt_hash', 64)->index();
    $table->json('recommendations')->nullable();
    $table->json('debt_strategy')->nullable();
    $table->json('key_insight')->nullable();
    $table->integer('input_tokens')->nullable();
    $table->integer('output_tokens')->nullable();
    $table->decimal('cost_usd', 8, 6)->nullable();
    $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
    $table->text('error_message')->nullable();
    $table->timestamps();

    $table->index('user_id');
    $table->index(['prompt_hash', 'created_at']);
});
```

### Progress Snapshots

```php
Schema::create('progress_snapshots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->date('snapshot_date');
    $table->bigInteger('net_worth');                     // stored in cents (can be negative)
    $table->bigInteger('total_debt');                    // stored in cents
    $table->bigInteger('total_invested');                // stored in cents
    $table->bigInteger('total_savings');                 // stored in cents
    $table->bigInteger('emergency_fund');                // stored in cents
    $table->bigInteger('monthly_income');                // stored in cents
    $table->bigInteger('monthly_expenses');              // stored in cents
    $table->decimal('savings_rate_pct', 5, 2);
    $table->bigInteger('freedom_number');                // stored in cents
    $table->decimal('freedom_pct_achieved', 6, 2);      // e.g., 12.50 for 12.5%
    $table->integer('estimated_months_to_freedom')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'snapshot_date']);
    $table->unique(['user_id', 'snapshot_date']);
});
```

---

## 13. API ROUTE MAP

```php
// routes/api.php

// === AUTH (Vuexy default + Sanctum) ===
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// === PUBLIC: Freedom Number Calculator (no auth required) ===
Route::post('/calculator/freedom-number', [FreedomCalculatorController::class, 'calculate']);

// === AUTHENTICATED ROUTES ===
Route::middleware('auth:sanctum')->group(function () {

    // Financial Profile
    Route::get('/profile', [FinancialProfileController::class, 'show']);
    Route::post('/profile', [FinancialProfileController::class, 'store']);
    Route::put('/profile', [FinancialProfileController::class, 'update']);

    // Expenses (CRUD)
    Route::apiResource('expenses', ExpenseController::class);
    Route::post('/expenses/bulk', [ExpenseController::class, 'bulkStore']);

    // Debts (CRUD)
    Route::apiResource('debts', DebtController::class);

    // Investment Accounts (CRUD)
    Route::apiResource('investment-accounts', InvestmentAccountController::class);

    // Freedom Calculator (authenticated — saves to history)
    Route::post('/calculator/freedom-number/save', [FreedomCalculatorController::class, 'calculateAndSave']);
    Route::get('/calculator/history', [FreedomCalculatorController::class, 'history']);

    // AI Recommendations
    Route::post('/recommendations/generate', [RecommendationController::class, 'generate']);
    Route::get('/recommendations/latest', [RecommendationController::class, 'latest']);
    Route::get('/recommendations/{id}', [RecommendationController::class, 'show']);
    Route::get('/recommendations/{id}/status', [RecommendationController::class, 'status']);

    // Progress Tracking
    Route::get('/progress', [ProgressController::class, 'index']);
    Route::post('/progress', [ProgressController::class, 'store']);
    Route::get('/progress/summary', [ProgressController::class, 'summary']);
    Route::get('/progress/latest', [ProgressController::class, 'latest']);
});
```

---

## 14. DOMAIN RULES & CONSTRAINTS

These rules MUST be enforced in code. The agent should reference these when building any feature:

### Financial Calculation Rules

1. **Freedom Number = Annual Expenses / Withdrawal Rate** — always show a range (3%, 4%, 5%, 6%)
2. **4% is the default** "safe withdrawal rate" based on the Trinity Study
3. **Inflation default: 3%** — adjustable by user
4. **Default investment return assumptions:** Conservative (5%), Moderate (7%), Aggressive (10%)
5. **Emergency fund target: 6 months of essential expenses** (not total expenses)
6. **Debt payoff: always calculate BOTH avalanche and snowball**, default display is avalanche
7. **Account contribution order:** 401K to employer match → HSA → Roth IRA → 401K to max → Taxable brokerage
8. **Federal student loans**: flag separately — may qualify for IDR, PSLF
9. **Never assume a tax rate** — ask for filing status and state, calculate brackets
10. **All monetary displays: US dollars, 2 decimal places, comma-separated** ($1,234,567.89)

### UI/UX Rules

1. **Freedom Number display must feel like a "gut punch"** — big, bold, impossible to ignore
2. **The gap visualization must be motivating, not depressing** — show the path, not just the distance
3. **Each recommendation must show its timeline impact** — "This saves you X years"
4. **Lever toggling must update the timeline in real-time** (client-side recalculation from PHP-precomputed data)
5. **Monthly update form must take under 60 seconds** — pre-fill everything, user only changes what's different
6. **Never display raw cents to users** — always format as dollars at the view layer
7. **Always show a disclaimer**: "This is for informational purposes only. Not financial advice."

### Security & Privacy Rules

1. **No PII to OpenRouter** — ever. Sanitize before every AI call.
2. **All sensitive financial data encrypted at rest** (Laravel's `encrypted` cast on model attributes)
3. **HTTPS only** — Railway provides this by default
4. **Rate limit all API endpoints** — Laravel's built-in throttle middleware
5. **CSRF protection** on all state-changing requests (Sanctum handles this for SPA)

---

## 15. TESTING STRATEGY

### Required: Test All Financial Calculations

Financial math is the core of the product. **Every calculation service MUST have unit tests.**

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

### Test Files to Create

**Unit Tests (app/Services):**
- `FreedomNumberCalculatorTest.php` — test at multiple expense levels and withdrawal rates, verify bcmath precision
- `DebtPayoffOptimizerTest.php` — test avalanche vs snowball ordering, interest calculations, edge cases (0% interest, single debt)
- `TimelineProjectionServiceTest.php` — test projection accuracy, handle zero savings rate, negative net worth
- `RetirementOptimizerTest.php` — test employer match calculations, contribution order logic

**Feature Tests (API endpoints):**
- `FreedomCalculatorTest.php` — test calculator endpoint with valid/invalid inputs
- `RecommendationFlowTest.php` — test full flow: profile → generate → status → results
- `ProgressTrackingTest.php` — test snapshot creation, summary calculations

### Test Assertions for Financial Precision

```php
// ALWAYS use assertSame for financial values — NOT assertEquals
// assertEquals does loose comparison which can miss precision errors
$this->assertSame('960000.00', $result->freedom_number); // strict type + value
```

---

## 16. DEPLOYMENT TO RAILWAY

### Railway Project Structure

```
Railway Project: FreedomStack
├── Service: app (Laravel — php-fpm + nginx)
├── Service: worker (Laravel Queue Worker)
├── Service: cron (Laravel Scheduler)
└── Database: PostgreSQL 17
```

### Railway Configuration Files

**railway.json:**
```json
{
  "$schema": "https://railway.com/railway.schema.json",
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan config:cache && php artisan route:cache && php-fpm",
    "healthcheckPath": "/api/health",
    "healthcheckTimeout": 30
  }
}
```

**Procfile (for worker and cron services):**
```
worker: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
cron: php artisan schedule:work
```

### Required Environment Variables on Railway

```env
APP_NAME=FreedomStack
APP_ENV=production
APP_KEY=base64:generate-with-artisan
APP_DEBUG=false
APP_URL=https://your-domain.up.railway.app

DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}

LOG_CHANNEL=stderr
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

OPENAI_API_KEY=sk-or-v1-your-openrouter-key
OPENAI_BASE_URL=https://openrouter.ai/api/v1
OPENROUTER_DEFAULT_MODEL=deepseek/deepseek-v3.2

# Phase 2
# STRIPE_KEY=pk_live_...
# STRIPE_SECRET=sk_live_...
# STRIPE_WEBHOOK_SECRET=whsec_...
```

### Health Check Endpoint

```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
});
```

### Estimated Monthly Costs

| Service | Cost |
|---------|------|
| Railway Hobby Plan | $5/month (includes credits) |
| OpenRouter (DeepSeek V3.2, ~1K users) | $1-5/month |
| Domain | ~$1/month |
| **Total** | **~$7-11/month** |

---

## 17. MONETIZATION ARCHITECTURE

### Principles (Non-Negotiable)

1. **No ads. Ever.** The FIRE community equates ads with "you are the product."
2. **Transparent pricing.** No dark patterns, no hidden fees, no bait-and-switch.
3. **Lifetime option available.** This signals alignment with FIRE values.
4. **Affiliate recommendations are always independent.** Never recommend a product because it pays you more. Recommend the best product, then provide an affiliate link if one exists.
5. **Publish a "Recommendation Independence Policy"** on the site explaining that AI recommendations are generated without affiliate influence.

### Tier Structure

**Free (no account needed):**
- Basic freedom number calculator
- Debt payoff comparison (avalanche vs snowball)
- 1 AI recommendation summary per session

**Premium ($8-10/month | $79-89/year | $399 lifetime):**
- Full AI action plan with unlimited refreshes
- Detailed debt optimization strategies
- Tax-advantaged account optimization
- Progress tracking dashboard with unlimited history
- Advanced scenario modeling (what-if analysis)
- Priority AI model (Claude Sonnet vs DeepSeek)
- Monthly email digest with personalized insights
- Export data (CSV, PDF reports)

### Future Revenue Streams (Ethical Only)

- **Affiliate partnerships** with FIRE-community-trusted products: student loan refinancing (SoFi, Earnest), low-cost brokerages (Vanguard, Fidelity), HYSAs (Marcus, Ally). Disclosed transparently.
- **Premium content/courses** on financial independence (optional add-on)
- **API access** for financial advisors who want to use the engine for their clients

---

## QUICK REFERENCE: AGENT COMMANDS

When the developer asks the agent to build something, the agent should:

1. **Check the Vuexy template first** — scan for existing components
2. **Check this document** for architecture rules and domain constraints
3. **Check the custom project skill** (`.cursor/skills/freedomstack/SKILL.md`)
4. **Use bcmath for ALL financial math** — never floats, never JavaScript
5. **Never send PII to OpenRouter** — sanitize all data
6. **Never let the LLM do math** — PHP calculates, LLM explains
7. **Store money as cents (integers)** — format as dollars only at display layer
8. **Test all financial calculations** — unit tests are mandatory for Services
9. **Use Vuexy components** — don't reinvent the wheel
10. **Keep it async** — AI calls go through the queue, never block HTTP

---

*This document is the single source of truth for building FreedomStack. When in doubt, reference it.*