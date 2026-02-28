<script setup lang="ts">
/**
 * Dashboard — FREEDOMSTACK_DASHBOARD_INSTRUCTIONS.md + freedomstack-dashboard-v2.html
 * Data-driven: welcome, profile completeness, stat cards, check-in nudge, 2x2 content grid.
 */
import { useDashboardBadgesStore } from '@core/stores/dashboardBadges'

definePage({
  meta: {
    layout: 'dashboard-shell',
  },
})

const dashboard = ref<{
  welcome: { title: string; subtitle: string }
  profile_completeness: { steps: any[]; pct: number; complete: boolean }
  stats: any
  checkin: any
  expenses_summary: { total_cents: number; by_category: { category: string; amount_cents: number }[] }
  debts: any[]
  has_no_debts_flag: boolean
  recommendations_card: { state: string; title?: string; subtitle?: string; items: any[] }
  journey: { snapshot_date: string; net_worth_cents: number }[]
  badges: { action_plan_count: number; progress_needs_update: boolean; profile_incomplete: boolean }
} | null>(null)
const loading = ref(true)
const checkinDismissed = ref(false)
const loadError = ref<number | null>(null)
const router = useRouter()

function formatCents(cents: number): string {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(cents / 100)
}

function formatPct(pct: number): string {
  return pct.toFixed(1) + '%'
}

function yearsToFreedomLabel(years: number | null, months: number | null): string {
  if (years == null && months == null) return '—'
  const y = years ?? 0
  const m = months ?? 0
  const parts: string[] = []
  if (y > 0) parts.push(`${y} yr`)
  if (m > 0) parts.push(`${m} mo`)
  return parts.length ? parts.join(' ') : '—'
}

function debtAprClass(apr: number): string {
  if (apr > 15) return 'high'
  if (apr >= 5) return 'med'
  return 'low'
}

function categoryLabel(cat: string): string {
  return cat.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const dashboardBadges = useDashboardBadgesStore()

async function loadDashboard() {
  loading.value = true
  loadError.value = null
  try {
    const res = await $api<{ data: any }>('/dashboard')
    dashboard.value = res.data
    if (res.data?.badges)
      dashboardBadges.setBadges(res.data.badges)
  } catch (e: any) {
    dashboard.value = null
    const status = e?.response?.status ?? e?.statusCode ?? e?.status ?? null
    loadError.value = status ?? 0
    if (status === 401 || status === 403) {
      await router.replace({
        name: 'login',
        query: { to: '/dashboard' },
      })
      return
    }
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)

const showProfileCompleteness = computed(() =>
  dashboard.value && !dashboard.value.profile_completeness.complete,
)

const showCheckinCard = computed(() => {
  if (!dashboard.value?.checkin) return false
  if (dashboard.value.checkin.state === 'getting_started' && checkinDismissed.value) return false
  return true
})

const topCategories = computed(() => {
  if (!dashboard.value?.expenses_summary?.by_category) return []
  return dashboard.value.expenses_summary.by_category.slice(0, 4)
})

const otherCents = computed(() => {
  if (!dashboard.value?.expenses_summary) return 0
  const total = dashboard.value.expenses_summary.total_cents
  const top = topCategories.value.reduce((s: number, c: { category: string; amount_cents: number }) => s + c.amount_cents, 0)
  return Math.max(0, total - top)
})
</script>

<template>
  <div class="dashboard-page" data-dashboard-version="v2">
    <template v-if="loading">
      <VProgressLinear indeterminate color="primary" class="mb-4" />
      <p class="text-medium-emphasis">Loading dashboard...</p>
    </template>

    <template v-else-if="dashboard">
      <!-- 1. Welcome (prototype + §2) -->
      <div class="dash-welcome mb-6">
        <h1 class="dash-welcome-title">
          {{ dashboard.welcome.title }}
        </h1>
        <p class="dash-welcome-sub">
          {{ dashboard.welcome.subtitle }}
        </p>
      </div>

      <!-- 2. Profile completeness (§1) — only when incomplete (prototype order) -->
      <div
        v-if="showProfileCompleteness"
        class="profile-completion mb-6"
      >
        <div class="pc-header">
          <h3 class="pc-title">Complete your profile to unlock your full action plan</h3>
          <span class="pc-pct">{{ dashboard.profile_completeness.pct }}% complete</span>
        </div>
        <div class="pc-bar-track">
          <div
            class="pc-bar-fill"
            :style="{ width: dashboard.profile_completeness.pct + '%' }"
          />
        </div>
        <div class="pc-steps">
          <div
            v-for="step in dashboard.profile_completeness.steps"
            :key="step.id"
            class="pc-step"
            :class="{ done: step.done }"
            @click="step.done ? null : (step.route ? $router.push(step.route) : null)"
          >
            <div
              class="pc-step-icon"
              :class="step.done ? 'complete' : 'pending'"
            >
              {{ step.done ? '✓' : step.id }}
            </div>
            <div class="pc-step-text">
              <div class="pc-step-title">{{ step.title }}</div>
              <div class="pc-step-sub">{{ step.sub }}</div>
            </div>
            <span
              v-if="step.time && !step.done"
              class="pc-step-time"
            >{{ step.time }}</span>
          </div>
        </div>
      </div>

      <!-- 3. Stat cards row (§3) — prototype: .stats-row grid 4 cols -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-top">
            <span class="stat-label">Freedom Number</span>
            <div class="stat-icon green">
              <VIcon icon="tabler-currency-dollar" size="18" />
            </div>
          </div>
          <div class="stat-value stat-value-green">
            {{ dashboard.stats.freedom_number.value_cents != null ? formatCents(dashboard.stats.freedom_number.value_cents) : '—' }}
          </div>
          <div class="stat-delta neutral">{{ dashboard.stats.freedom_number.subtitle }}</div>
        </div>
        <div class="stat-card" :class="{ 'needs-data': dashboard.stats.net_worth.needs_data }">
          <div class="stat-top">
            <span class="stat-label">Net Worth</span>
            <div class="stat-icon amber">
              <VIcon icon="tabler-wallet" size="18" />
            </div>
          </div>
          <div class="stat-value" :class="{ muted: dashboard.stats.net_worth.needs_data }">
            {{ dashboard.stats.net_worth.value_cents != null ? formatCents(dashboard.stats.net_worth.value_cents) : '—' }}
          </div>
          <div v-if="dashboard.stats.net_worth.cta_route" class="stat-delta">
            <RouterLink :to="dashboard.stats.net_worth.cta_route" class="stat-cta">Add debts & savings to calculate →</RouterLink>
          </div>
          <div v-else-if="dashboard.stats.net_worth.delta?.label" class="stat-delta" :class="dashboard.stats.net_worth.delta.value != null ? (dashboard.stats.net_worth.delta.value >= 0 ? 'up' : 'down') : 'neutral'">
            <template v-if="dashboard.stats.net_worth.delta.value != null">
              {{ dashboard.stats.net_worth.delta.value >= 0 ? '↑' : '↓' }} {{ formatCents(Math.abs(dashboard.stats.net_worth.delta.value)) }} since last
            </template>
            <template v-else>{{ dashboard.stats.net_worth.delta.label }}</template>
          </div>
        </div>
        <div class="stat-card" :class="{ 'needs-data': dashboard.stats.freedom_progress.needs_data }">
          <div class="stat-top">
            <span class="stat-label">Freedom Progress</span>
            <div class="stat-icon blue">
              <VIcon icon="tabler-chart-line" size="18" />
            </div>
          </div>
          <div class="stat-value" :class="{ muted: dashboard.stats.freedom_progress.needs_data }">
            {{ dashboard.stats.freedom_progress.value_pct != null ? formatPct(dashboard.stats.freedom_progress.value_pct) : '—' }}
          </div>
          <div v-if="dashboard.stats.freedom_progress.delta?.label" class="stat-delta" :class="dashboard.stats.freedom_progress.delta.value != null ? (dashboard.stats.freedom_progress.delta.value >= 0 ? 'up' : 'down') : 'neutral'">
            <template v-if="dashboard.stats.freedom_progress.delta.value != null">
              {{ dashboard.stats.freedom_progress.delta.value >= 0 ? '↑' : '↓' }} {{ dashboard.stats.freedom_progress.delta.value }}% since last
            </template>
            <template v-else>{{ dashboard.stats.freedom_progress.delta.label }}</template>
          </div>
        </div>
        <div class="stat-card" :class="{ 'needs-data': dashboard.stats.time_to_freedom.needs_data }">
          <div class="stat-top">
            <span class="stat-label">Time to Freedom</span>
            <div class="stat-icon green">
              <VIcon icon="tabler-clock" size="18" />
            </div>
          </div>
          <div class="stat-value" :class="{ muted: dashboard.stats.time_to_freedom.needs_data }">
            {{ yearsToFreedomLabel(dashboard.stats.time_to_freedom.years, dashboard.stats.time_to_freedom.months) }}
          </div>
          <div v-if="dashboard.stats.time_to_freedom.cta_route" class="stat-delta">
            <RouterLink :to="dashboard.stats.time_to_freedom.cta_route" class="stat-cta">Add income to calculate →</RouterLink>
          </div>
          <div v-else-if="dashboard.stats.time_to_freedom.delta?.label" class="stat-delta" :class="dashboard.stats.time_to_freedom.delta.value != null ? (dashboard.stats.time_to_freedom.delta.value > 0 ? 'up' : 'down') : 'neutral'">
            <template v-if="dashboard.stats.time_to_freedom.delta.value != null">
              {{ dashboard.stats.time_to_freedom.delta.value > 0 ? '↑' : '↓' }} {{ Math.abs(dashboard.stats.time_to_freedom.delta.value) }} mo faster
            </template>
            <template v-else>{{ dashboard.stats.time_to_freedom.delta.label }}</template>
          </div>
        </div>
      </div>

      <!-- 4. Check-in nudge (§4) -->
      <div v-if="showCheckinCard && dashboard.checkin" class="mb-6">
        <div
          class="checkin-card"
          :class="dashboard.checkin.state === 'nudge' || dashboard.checkin.state === 'first' ? 'nudge' : 'ok'"
        >
          <div class="checkin-icon" :class="dashboard.checkin.state === 'nudge' || dashboard.checkin.state === 'first' ? 'amber' : 'green'">
            <VIcon
              :icon="dashboard.checkin.state === 'ok' ? 'tabler-check' : 'tabler-calendar'"
              size="20"
            />
          </div>
          <div class="checkin-content">
            <div class="checkin-title">{{ dashboard.checkin.title }}</div>
            <div class="checkin-sub">{{ dashboard.checkin.subtitle }}</div>
          </div>
          <VBtn
            v-if="dashboard.checkin.button_text && (dashboard.checkin.state !== 'getting_started' || !checkinDismissed)"
            :color="dashboard.checkin.state === 'nudge' || dashboard.checkin.state === 'first' ? 'warning' : 'success'"
            variant="flat"
            size="small"
            @click="dashboard.checkin.state === 'getting_started' ? (checkinDismissed = true) : $router.push(dashboard.checkin.button_route)"
          >
            {{ dashboard.checkin.button_text }}
          </VBtn>
          <VBtn
            v-else-if="dashboard.checkin.state === 'ok'"
            variant="text"
            size="small"
            color="primary"
            :to="'/progress'"
          >
            View progress →
          </VBtn>
        </div>
      </div>

      <!-- 5. Content grid 2x2 (§5) — prototype: .content-grid grid 1fr 1fr -->
      <div class="content-grid">
        <!-- Your journey -->
        <div class="content-card-wrap">
          <div class="content-card">
            <div class="card-header">
              <span class="card-title">Your journey</span>
              <RouterLink to="/progress" class="card-link">View all →</RouterLink>
            </div>
            <div v-if="dashboard.journey.length < 2" class="chart-placeholder">
              <p class="text-medium-emphasis">Log monthly check-ins to see your progress over time. Your first data point starts the chart.</p>
            </div>
            <div v-else class="chart-fake">
              <!-- TODO: ApexCharts area chart when data exists -->
              <p class="text-caption text-medium-emphasis">{{ dashboard.journey.length }} snapshots</p>
            </div>
          </div>
        </div>

        <!-- Monthly expenses -->
        <div class="content-card-wrap">
          <div class="content-card">
            <div class="card-header">
              <span class="card-title">Monthly expenses</span>
              <RouterLink to="/finances/expenses" class="card-link">Manage →</RouterLink>
            </div>
            <div class="donut-wrap">
              <div class="donut-placeholder">
                <div class="donut-center">
                  <div class="pct">{{ formatCents(dashboard.expenses_summary.total_cents) }}</div>
                  <div class="pct-label">/ month</div>
                </div>
              </div>
              <div class="legend">
                <div v-for="c in topCategories" :key="c.category" class="legend-item">
                  <span class="legend-dot" />
                  {{ categoryLabel(c.category) }} {{ formatCents(c.amount_cents) }}
                </div>
                <div v-if="otherCents > 0" class="legend-item">
                  <span class="legend-dot legend-dot-other" />
                  Other {{ formatCents(otherCents) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top recommendations -->
        <div class="content-card-wrap">
          <div class="content-card">
            <div class="card-header">
              <span class="card-title">Top recommendations</span>
              <RouterLink v-if="dashboard.recommendations_card.state === 'populated'" to="/action-plan" class="card-link">Full plan →</RouterLink>
            </div>
            <div v-if="dashboard.recommendations_card.state === 'locked'" class="recs-locked">
              <VIcon icon="tabler-lock" size="32" class="mb-2" />
              <div class="recs-locked-title">{{ dashboard.recommendations_card.title }}</div>
              <div class="recs-locked-sub">{{ dashboard.recommendations_card.subtitle }}</div>
            </div>
            <div v-else-if="dashboard.recommendations_card.state === 'loading'" class="recs-loading">
              <VProgressCircular indeterminate size="32" width="2" color="primary" class="mb-2" />
              <span>Generating your action plan...</span>
            </div>
            <div v-else-if="dashboard.recommendations_card.state === 'populated' && dashboard.recommendations_card.items.length" class="rec-mini">
              <RouterLink
                v-for="(item, idx) in dashboard.recommendations_card.items"
                :key="item.id"
                :to="'/action-plan'"
                class="rec-mini-item"
              >
                <span class="rec-num">{{ idx + 1 }}</span>
                <div class="rec-mini-content">
                  <div class="rec-mini-title">{{ item.title }}</div>
                  <div v-if="item.impact" class="rec-mini-impact">{{ item.impact }}</div>
                </div>
              </RouterLink>
            </div>
            <div v-else class="recs-empty text-medium-emphasis text-caption">No recommendations yet.</div>
          </div>
        </div>

        <!-- Debt snapshot -->
        <div class="content-card-wrap">
          <div class="content-card">
            <div class="card-header">
              <span class="card-title">Debt snapshot</span>
              <RouterLink to="/finances/debts" class="card-link">Details →</RouterLink>
            </div>
            <div v-if="dashboard.has_no_debts_flag && !dashboard.debts.length" class="debt-empty-ok">
              No debts — that's a strong starting position.
            </div>
            <div v-else-if="!dashboard.debts.length" class="debt-empty">
              <div class="debt-empty-title">No debts added yet</div>
              <div class="debt-empty-sub">Add your debts to see payoff strategies and optimization opportunities.</div>
              <RouterLink to="/finances/debts" class="stat-cta mt-2 d-inline-block">Add your first debt →</RouterLink>
            </div>
            <div v-else class="debt-bars">
              <div v-for="d in dashboard.debts" :key="d.id" class="debt-bar-item">
                <div class="debt-bar-top">
                  <span class="name">{{ d.name }}</span>
                  <span class="amount">{{ formatCents(d.balance_cents) }}</span>
                </div>
                <div class="debt-bar-track">
                  <div class="debt-bar-fill" :class="debtAprClass(d.apr)" :style="{ width: Math.min(100, d.payoff_pct) + '%' }" />
                </div>
              </div>
              <div class="debt-total">
                <span class="label">Total remaining</span>
                <span class="amount">{{ formatCents(dashboard.debts.reduce((s, d) => s + d.balance_cents, 0)) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <VAlert type="error" variant="tonal" class="mb-4">
        <span v-if="loadError === 401 || loadError === 403">Please log in to view your dashboard.</span>
        <span v-else>Could not load dashboard.</span>
        <template #append>
          <VBtn
            v-if="loadError !== 401 && loadError !== 403"
            variant="text"
            size="small"
            color="error"
            @click="loadDashboard"
          >
            Retry
          </VBtn>
          <VBtn
            v-else
            variant="tonal"
            size="small"
            :to="{ name: 'login', query: { to: '/dashboard' } }"
          >
            Log in
          </VBtn>
        </template>
      </VAlert>
    </template>
  </div>
</template>

<style lang="scss" scoped>
/* Match freedomstack-dashboard-v2.html prototype */
.dashboard-page {
  --fs-black: #0a0a0a;
  --fs-black-card: #111111;
  --fs-white: #fafaf9;
  --fs-green: #22c55e;
  --fs-green-glow: rgba(34, 197, 94, 0.12);
  --fs-red: #ef4444;
  --fs-amber: #f59e0b;
  --fs-amber-glow: rgba(245, 158, 11, 0.12);
  --fs-blue: #3b82f6;
  --fs-blue-glow: rgba(59, 130, 246, 0.12);
  --fs-gray-300: #d4d4d8;
  --fs-gray-400: #a1a1aa;
  --fs-gray-500: #71717a;
  --fs-gray-600: #52525b;
  background: var(--fs-black);
  color: var(--fs-white);
  min-height: 100%;
  padding: 0 1.5rem 2rem;
  /* Pull full width so dark background is visible */
  margin-left: -1.5rem;
  margin-right: -1.5rem;
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}

.dash-welcome-title {
  font-family: 'Instrument Serif', Georgia, serif;
  font-size: 1.75rem;
  letter-spacing: -0.02em;
  margin-bottom: 0.25rem;
  color: var(--fs-white);
}

.dash-welcome-sub {
  color: var(--fs-gray-400);
  font-size: 0.9rem;
}

.profile-completion {
  background: var(--fs-black-card);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 1.5rem;
}

.pc-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.pc-title {
  font-size: 0.95rem;
  font-weight: 600;
}

.pc-pct {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.8rem;
  color: var(--fs-green);
}

.pc-bar-track {
  width: 100%;
  height: 6px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 100px;
  margin-bottom: 1.25rem;
  overflow: hidden;
}

.pc-bar-fill {
  height: 100%;
  background: var(--fs-green);
  border-radius: 100px;
  transition: width 0.6s ease;
}

.pc-steps {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.75rem;
}

.pc-step {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  cursor: pointer;
}

.pc-step.done {
  opacity: 0.5;
  cursor: default;
}

.pc-step-icon {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.7rem;
  font-weight: 700;
}

.pc-step-icon.pending {
  background: rgba(255, 255, 255, 0.06);
  color: var(--fs-gray-400);
  border: 1.5px solid rgba(255, 255, 255, 0.1);
}

.pc-step-icon.complete {
  background: var(--fs-green-glow);
  color: var(--fs-green);
}

.pc-step-title { font-size: 0.82rem; font-weight: 500; }
.pc-step-sub { font-size: 0.7rem; color: var(--fs-gray-500); }
.pc-step-time { font-family: 'JetBrains Mono', monospace; font-size: 0.65rem; color: var(--fs-gray-600); flex-shrink: 0; }

.stat-card {
  background: var(--fs-black-card);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 1.35rem;
  height: 100%;
  transition: border-color 0.2s;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card:hover {
  border-color: rgba(255, 255, 255, 0.1);
}

.stat-card.needs-data {
  border-style: dashed;
  border-color: rgba(255, 255, 255, 0.08);
}

.stat-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.stat-label { font-size: 0.78rem; color: var(--fs-gray-500); }

.stat-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon.green { background: var(--fs-green-glow); color: var(--fs-green); }
.stat-icon.amber { background: var(--fs-amber-glow); color: var(--fs-amber); }
.stat-icon.blue { background: var(--fs-blue-glow); color: var(--fs-blue); }

.stat-value {
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.5rem;
  font-weight: 600;
  letter-spacing: -0.02em;
  margin-bottom: 0.15rem;
}

.stat-value-green { color: var(--fs-green); }
.stat-value.muted { color: var(--fs-gray-600); }

.stat-delta {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.72rem;
}

.stat-delta.up { color: var(--fs-green); }
.stat-delta.down { color: var(--fs-red); }
.stat-delta.neutral { color: var(--fs-gray-500); }

.stat-cta {
  color: var(--fs-green);
  text-decoration: none;
  font-size: 0.78rem;
}

.stat-cta:hover { opacity: 0.85; }

.checkin-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.15rem 1.25rem;
  border-radius: 12px;
}

.checkin-card.nudge {
  background: rgba(245, 158, 11, 0.04);
  border: 1px solid rgba(245, 158, 11, 0.12);
}

.checkin-card.ok {
  background: rgba(34, 197, 94, 0.04);
  border: 1px solid rgba(34, 197, 94, 0.12);
}

.checkin-icon {
  width: 38px;
  height: 38px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.checkin-icon.amber { background: var(--fs-amber-glow); color: var(--fs-amber); }
.checkin-icon.green { background: var(--fs-green-glow); color: var(--fs-green); }

.checkin-title { font-size: 0.85rem; font-weight: 600; margin-bottom: 0.1rem; }
.checkin-sub { font-size: 0.75rem; color: var(--fs-gray-500); }

.content-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.content-card-wrap {
  min-width: 0;
}

.content-card {
  background: var(--fs-black-card);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 1.5rem;
  height: 100%;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.card-title { font-size: 0.95rem; font-weight: 600; }

.card-link {
  font-size: 0.78rem;
  color: var(--fs-green);
  text-decoration: none;
}

.card-link:hover { opacity: 0.85; }

.chart-placeholder,
.chart-fake {
  min-height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  text-align: center;
}

.donut-wrap {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.donut-placeholder {
  width: 120px;
  height: 120px;
  position: relative;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.donut-center {
  position: absolute;
  text-align: center;
}

.donut-center .pct { font-family: 'JetBrains Mono', monospace; font-size: 1.2rem; font-weight: 600; color: var(--fs-green); }
.donut-center .pct-label { font-size: 0.68rem; color: var(--fs-gray-500); }

.legend { font-size: 0.78rem; display: flex; flex-direction: column; gap: 0.4rem; }
.legend-item { display: flex; align-items: center; gap: 0.5rem; }
.legend-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
.legend-item:nth-child(1) .legend-dot { background: var(--fs-green); }
.legend-item:nth-child(2) .legend-dot { background: var(--fs-blue); }
.legend-item:nth-child(3) .legend-dot { background: var(--fs-amber); }
.legend-item:nth-child(4) .legend-dot { background: var(--fs-red); }
.legend-dot-other { background: var(--fs-gray-500); }

.recs-locked,
.recs-loading {
  text-align: center;
  padding: 1.5rem;
  color: var(--fs-gray-500);
  font-size: 0.85rem;
}

.recs-locked-title,
.recs-loading span { color: var(--fs-gray-400); font-weight: 500; margin-bottom: 0.25rem; }
.recs-loading { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }

.rec-mini { display: flex; flex-direction: column; gap: 0.65rem; }

.rec-mini-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s;
}

.rec-mini-item:hover { border-color: rgba(255, 255, 255, 0.08); background: rgba(255, 255, 255, 0.03); }

.rec-num {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.65rem;
  font-weight: 600;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  background: var(--fs-green-glow);
  color: var(--fs-green);
  flex-shrink: 0;
}

.rec-mini-title { font-size: 0.85rem; font-weight: 500; }
.rec-mini-impact { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--fs-green); }

.recs-empty { padding: 1rem 0; }

.debt-empty-ok {
  padding: 1.5rem 0;
  text-align: center;
  color: var(--fs-gray-500);
  font-size: 0.9rem;
}

.debt-empty { padding: 1rem 0; text-align: center; }
.debt-empty-title { color: var(--fs-gray-400); font-weight: 500; margin-bottom: 0.25rem; }
.debt-empty-sub { font-size: 0.85rem; color: var(--fs-gray-500); }

.debt-bars { display: flex; flex-direction: column; gap: 0.75rem; }
.debt-bar-item { display: flex; flex-direction: column; gap: 0.3rem; }
.debt-bar-top { display: flex; justify-content: space-between; font-size: 0.8rem; }
.debt-bar-top .name { color: var(--fs-gray-300, #d4d4d8); }
.debt-bar-top .amount { font-family: 'JetBrains Mono', monospace; font-size: 0.78rem; color: var(--fs-gray-400); }
.debt-bar-track { width: 100%; height: 6px; background: rgba(255, 255, 255, 0.06); border-radius: 100px; overflow: hidden; }
.debt-bar-fill { height: 100%; border-radius: 100px; }
.debt-bar-fill.high { background: var(--fs-red); }
.debt-bar-fill.med { background: var(--fs-amber); }
.debt-bar-fill.low { background: var(--fs-green); }
.debt-total { display: flex; justify-content: space-between; padding-top: 0.75rem; margin-top: 0.5rem; border-top: 1px solid rgba(255, 255, 255, 0.05); font-size: 0.82rem; }
.debt-total .label { color: var(--fs-gray-500); }
.debt-total .amount { font-family: 'JetBrains Mono', monospace; font-weight: 500; }

@media (max-width: 1100px) {
  .stats-row { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .content-grid { grid-template-columns: 1fr; }
  .checkin-card { flex-direction: column; text-align: center; }
}

@media (max-width: 600px) {
  .stats-row { grid-template-columns: 1fr; }
  .pc-steps { grid-template-columns: 1fr; }
}
</style>
