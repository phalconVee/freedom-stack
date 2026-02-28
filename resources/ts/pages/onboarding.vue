<script setup lang="ts">
/**
 * Onboarding — FREEDOMSTACK_USER_JOURNEY.md §3.2
 * 4 steps: About You, Income, Debts, Savings & Investments. Skippable. No sidebar.
 */
definePage({
  meta: {
    layout: 'blank',
  },
})

const route = useRoute()
const router = useRouter()

const step = computed({
  get: () => Math.min(4, Math.max(1, parseInt(String(route.query.step || '1'), 10) || 1)),
  set: (s: number) => router.replace({ query: { step: String(s) } }),
})

// Step 1: About You
const about = ref({
  current_age: '',
  target_freedom_age: '',
  filing_status: 'single',
  state_of_residence: '',
  risk_tolerance: 'moderate',
})

const filingOptions = [
  { value: 'single', title: 'Single' },
  { value: 'married_joint', title: 'Married Filing Jointly' },
  { value: 'married_separate', title: 'Married Filing Separately' },
  { value: 'head_of_household', title: 'Head of Household' },
]

const riskOptions = [
  { value: 'conservative', title: 'Conservative', desc: "I prefer safety over growth. I'd rather earn less than risk losing money." },
  { value: 'moderate', title: 'Moderate', desc: "I'm okay with some ups and downs for better long-term returns." },
  { value: 'aggressive', title: 'Aggressive', desc: "I'm comfortable with high volatility for maximum growth potential." },
]

// Step 2: Income
const income = ref({
  monthly_gross_income: '',
  monthly_net_income: '',
  income_type: 'salaried',
  side_income: '',
})

const incomeTypeOptions = [
  { value: 'salaried', title: 'Salaried' },
  { value: 'hourly', title: 'Hourly' },
  { value: 'self_employed', title: 'Self-employed' },
  { value: 'mixed', title: 'Mixed' },
]

// Step 3: Debts (simplified — full CRUD can live on /finances/debts)
const debts = ref<Array<{
  type: string
  name: string
  balance: string
  apr: string
  min_payment: string
  is_federal_student_loan: boolean
}>>([])
const debtTypes = [
  'Student Loan', 'Credit Card', 'Auto Loan', 'Mortgage', 'Personal Loan', 'Medical Debt', 'Other',
]

// Step 4: Investments
const accounts = ref<Array<{
  type: string
  name: string
  balance: string
  monthly_contribution: string
  employer_match_pct: string
  employer_match_limit: string
}>>([])
const accountTypes = [
  '401(k)', '403(b)', 'Traditional IRA', 'Roth IRA', 'HSA', 'Brokerage', 'HYSA/Savings', 'Other',
]

const saving = ref(false)

function next() {
  if (step.value < 4)
    step.value = step.value + 1
  else
    finish()
}

const debtTypeMap: Record<string, string> = {
  'Student Loan': 'student_loan',
  'Credit Card': 'credit_card',
  'Auto Loan': 'auto_loan',
  'Mortgage': 'mortgage',
  'Personal Loan': 'personal_loan',
  'Medical Debt': 'medical',
  'Other': 'other',
}

const accountTypeMap: Record<string, string> = {
  '401(k)': '401k',
  '403(b)': '403b',
  'Traditional IRA': 'traditional_ira',
  'Roth IRA': 'roth_ira',
  'HSA': 'hsa',
  'Brokerage': 'brokerage',
  'HYSA/Savings': 'hysa',
  'Other': 'other',
}

async function finish() {
  saving.value = true
  try {
    const age = about.value.current_age ? parseInt(about.value.current_age, 10) : null
    const targetAge = about.value.target_freedom_age ? parseInt(about.value.target_freedom_age, 10) : null
    const gross = Math.round((parseFloat(income.value.monthly_gross_income) || 0) * 100)
    const net = Math.round((parseFloat(income.value.monthly_net_income) || 0) * 100)
    await $api('/profile', {
      method: 'POST',
      body: {
        current_age: age,
        target_fire_age: targetAge,
        filing_status: about.value.filing_status,
        state_of_residence: about.value.state_of_residence || null,
        risk_tolerance: about.value.risk_tolerance,
        monthly_gross_income: gross,
        monthly_net_income: net,
        monthly_expenses_total: 0, // required by API; user can set later in My Finances
      },
    })
    for (const d of debts.value) {
      if (!d.balance || parseFloat(d.balance) <= 0) continue
      await $api('/debts', {
        method: 'POST',
        body: {
          type: debtTypeMap[d.type] || 'other',
          name: d.name || d.type,
          balance: Math.round((parseFloat(d.balance) || 0) * 100),
          interest_rate: parseFloat(d.apr) || 0,
          minimum_payment: Math.round((parseFloat(d.min_payment) || 0) * 100),
          is_federal_student_loan: d.is_federal_student_loan,
        },
      })
    }
    for (const a of accounts.value) {
      if (!a.balance || parseFloat(a.balance) <= 0) continue
      await $api('/investment-accounts', {
        method: 'POST',
        body: {
          type: accountTypeMap[a.type] || 'other',
          name: a.name || a.type,
          balance: Math.round((parseFloat(a.balance) || 0) * 100),
          monthly_contribution: Math.round((parseFloat(a.monthly_contribution) || 0) * 100),
          employer_match_pct: parseFloat(a.employer_match_pct) || 0,
          employer_match_limit: a.employer_match_limit ? Math.round(parseFloat(a.employer_match_limit) * 100) : null,
        },
      })
    }
    await router.push('/gap-analysis')
  } catch (_) {
    saving.value = false
  }
}

function skip() {
  if (step.value < 4)
    step.value = step.value + 1
  else
    router.push('/dashboard')
}

function addDebt() {
  debts.value.push({
    type: 'Student Loan',
    name: '',
    balance: '',
    apr: '',
    min_payment: '',
    is_federal_student_loan: false,
  })
}

function removeDebt(i: number) {
  debts.value.splice(i, 1)
}

function addAccount() {
  accounts.value.push({
    type: '401(k)',
    name: '',
    balance: '',
    monthly_contribution: '',
    employer_match_pct: '',
    employer_match_limit: '',
  })
}

function removeAccount(i: number) {
  accounts.value.splice(i, 1)
}

const totalDebt = computed(() =>
  debts.value.reduce((s: number, d: (typeof debts.value)[0]) => s + (parseFloat(d.balance) || 0) * 100, 0))
const totalDebtPayments = computed(() =>
  debts.value.reduce((s: number, d: (typeof debts.value)[0]) => s + (parseFloat(d.min_payment) || 0) * 100, 0))
const totalSaved = computed(() =>
  accounts.value.reduce((s: number, a: (typeof accounts.value)[0]) => s + (parseFloat(a.balance) || 0) * 100, 0))
const totalContributions = computed(() =>
  accounts.value.reduce((s: number, a: (typeof accounts.value)[0]) => s + (parseFloat(a.monthly_contribution) || 0) * 100, 0))

function formatCents(c: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(c / 100)
}
</script>

<template>
  <div class="onboarding-page">
    <VContainer class="py-8">
      <div class="text-center mb-8">
      <p class="text-body-2 text-medium-emphasis">
        Step {{ step }} of 4 — Let's build your financial profile
      </p>
      <VProgressLinear
        :model-value="(step / 4) * 100"
        color="primary"
        height="6"
        rounded
        class="mx-auto mt-2"
        style="max-width: 320px;"
      />
    </div>

    <VRow justify="center">
      <VCol cols="12" md="8" lg="6">
        <!-- Step 1: About You -->
        <template v-if="step === 1">
          <h2 class="text-h5 mb-4">
            First, a bit about you
          </h2>
          <VCard>
            <VCardText>
              <VRow>
                <VCol cols="12" sm="6">
                  <AppTextField
                    v-model="about.current_age"
                    type="number"
                    min="18"
                    max="100"
                    label="Current age"
                  />
                </VCol>
                <VCol cols="12" sm="6">
                  <AppTextField
                    v-model="about.target_freedom_age"
                    type="number"
                    min="18"
                    max="100"
                    label="Target freedom age"
                    hint="What age would you like to be financially free?"
                    persistent-hint
                  />
                </VCol>
                <VCol cols="12" sm="6">
                  <AppSelect
                    v-model="about.filing_status"
                    label="Filing status"
                    :items="filingOptions"
                  />
                </VCol>
                <VCol cols="12" sm="6">
                  <AppTextField
                    v-model="about.state_of_residence"
                    label="State of residence (2-letter)"
                    maxlength="2"
                  />
                </VCol>
                <VCol cols="12">
                  <p class="text-body-2 text-medium-emphasis mb-2">
                    Risk tolerance
                  </p>
                  <div class="d-flex flex-wrap gap-3">
                    <VCard
                      v-for="opt in riskOptions"
                      :key="opt.value"
                      variant="outlined"
                      class="pa-3 cursor-pointer"
                      :class="{ 'border-primary': about.risk_tolerance === opt.value }"
                      @click="about.risk_tolerance = opt.value"
                    >
                      <div class="text-subtitle-1 font-weight-medium">
                        {{ opt.title }}
                      </div>
                      <div class="text-body-2 text-medium-emphasis">
                        {{ opt.desc }}
                      </div>
                    </VCard>
                  </div>
                </VCol>
              </VRow>
              <div class="d-flex justify-space-between mt-4">
                <VBtn variant="text" @click="skip">
                  Skip for now
                </VBtn>
                <VBtn color="primary" @click="next">
                  Next: Income →
                </VBtn>
              </div>
            </VCardText>
          </VCard>
        </template>

        <!-- Step 2: Income -->
        <template v-else-if="step === 2">
          <h2 class="text-h5 mb-4">
            What's coming in?
          </h2>
          <VCard>
            <VCardText>
              <p class="text-body-2 text-medium-emphasis mb-4">
                If you're not sure about your exact net income, check your last bank statement or pay stub.
              </p>
              <VRow>
                <VCol cols="12" sm="6">
                  <AppTextField
                    v-model="income.monthly_gross_income"
                    type="number"
                    min="0"
                    step="0.01"
                    label="Monthly gross income ($)"
                  />
                </VCol>
                <VCol cols="12" sm="6">
                  <AppTextField
                    v-model="income.monthly_net_income"
                    type="number"
                    min="0"
                    step="0.01"
                    label="Monthly net income ($)"
                  />
                </VCol>
                <VCol cols="12">
                  <AppSelect
                    v-model="income.income_type"
                    label="Income type"
                    :items="incomeTypeOptions"
                  />
                </VCol>
                <VCol cols="12">
                  <AppTextField
                    v-model="income.side_income"
                    type="number"
                    min="0"
                    step="0.01"
                    label="Side income (optional) — freelance, side hustle, rental, etc."
                  />
                </VCol>
              </VRow>
              <div class="d-flex justify-space-between mt-4">
                <VBtn variant="text" @click="skip">
                  Skip for now
                </VBtn>
                <VBtn color="primary" @click="next">
                  Next: Debts →
                </VBtn>
              </div>
            </VCardText>
          </VCard>
        </template>

        <!-- Step 3: Debts -->
        <template v-else-if="step === 3">
          <h2 class="text-h5 mb-4">
            What do you owe?
          </h2>
          <VCard>
            <VCardText>
              <VBtn variant="tonal" color="primary" class="mb-4" @click="addDebt">
                Add a Debt
              </VBtn>
              <template v-if="debts.length">
                <div
                  v-for="(d, i) in debts"
                  :key="i"
                  class="d-flex align-center justify-space-between pa-3 mb-3 rounded border"
                >
                  <div>
                    <span class="font-weight-medium">{{ d.type }}</span>
                    <span v-if="d.name"> — {{ d.name }}</span>
                    <div class="text-caption text-medium-emphasis">
                      Balance: {{ d.balance ? `$${d.balance}` : '—' }} | APR: {{ d.apr || '—' }}% | Min: {{ d.min_payment ? `$${d.min_payment}/mo` : '—' }}
                    </div>
                  </div>
                  <VBtn icon variant="text" size="small" @click="removeDebt(i)">
                    <VIcon icon="tabler-trash" />
                  </VBtn>
                </div>
                <p class="text-body-2 text-medium-emphasis">
                  Total debt: {{ formatCents(totalDebt) }} | Total monthly payments: {{ formatCents(totalDebtPayments) }}
                </p>
              </template>
              <template v-else>
                <p class="text-body-2 text-medium-emphasis mb-4">
                  No debts? That's amazing. Click next to continue.
                </p>
              </template>
              <div v-for="(d, i) in debts" :key="'form-' + i" class="pa-3 mb-3 rounded border">
                <VRow dense>
                  <VCol cols="12" sm="6">
                    <AppSelect
                      v-model="d.type"
                      label="Debt type"
                      :items="debtTypes.map(t => ({ value: t, title: t }))"
                    />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <AppTextField v-model="d.name" label="Name (e.g. Sallie Mae)" placeholder="e.g., Sallie Mae Federal Loan" />
                  </VCol>
                  <VCol cols="12" sm="4">
                    <AppTextField v-model="d.balance" type="number" min="0" step="0.01" label="Balance ($)" />
                  </VCol>
                  <VCol cols="12" sm="4">
                    <AppTextField v-model="d.apr" type="number" min="0" step="0.1" label="APR (%)" />
                  </VCol>
                  <VCol cols="12" sm="4">
                    <AppTextField v-model="d.min_payment" type="number" min="0" step="0.01" label="Min payment ($/mo)" />
                  </VCol>
                  <VCol v-if="d.type === 'Student Loan'" cols="12">
                    <VSwitch v-model="d.is_federal_student_loan" label="Federal student loan?" />
                  </VCol>
                </VRow>
              </div>
              <div class="d-flex justify-space-between mt-4">
                <VBtn variant="text" @click="skip">
                  Skip for now
                </VBtn>
                <VBtn color="primary" @click="next">
                  Next: Savings & Investments →
                </VBtn>
              </div>
            </VCardText>
          </VCard>
        </template>

        <!-- Step 4: Savings & Investments -->
        <template v-else>
          <h2 class="text-h5 mb-4">
            What have you built so far?
          </h2>
          <VCard>
            <VCardText>
              <VBtn variant="tonal" color="primary" class="mb-4" @click="addAccount">
                Add an Account
              </VBtn>
              <template v-if="accounts.length">
                <div
                  v-for="(a, i) in accounts"
                  :key="i"
                  class="d-flex align-center justify-space-between pa-3 mb-3 rounded border"
                >
                  <div>
                    <span class="font-weight-medium">{{ a.type }}</span>
                    <span v-if="a.name"> — {{ a.name }}</span>
                    <div class="text-caption text-medium-emphasis">
                      Balance: {{ a.balance ? `$${a.balance}` : '—' }} | Contribution: {{ a.monthly_contribution ? `$${a.monthly_contribution}/mo` : '—' }}
                    </div>
                  </div>
                  <VBtn icon variant="text" size="small" @click="removeAccount(i)">
                    <VIcon icon="tabler-trash" />
                  </VBtn>
                </div>
                <p class="text-body-2 text-medium-emphasis">
                  Total invested/saved: {{ formatCents(totalSaved) }} | Monthly contributions: {{ formatCents(totalContributions) }}
                </p>
              </template>
              <div v-for="(a, i) in accounts" :key="'form-' + i" class="pa-3 mb-3 rounded border">
                <VRow dense>
                  <VCol cols="12" sm="6">
                    <AppSelect
                      v-model="a.type"
                      label="Account type"
                      :items="accountTypes.map(t => ({ value: t, title: t }))"
                    />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <AppTextField v-model="a.name" label="Name" placeholder="e.g., Fidelity 401K" />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <AppTextField v-model="a.balance" type="number" min="0" step="0.01" label="Balance ($)" />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <AppTextField v-model="a.monthly_contribution" type="number" min="0" step="0.01" label="Monthly contribution ($)" />
                  </VCol>
                  <VCol v-if="['401(k)', '403(b)'].includes(a.type)" cols="12" sm="6">
                    <AppTextField v-model="a.employer_match_pct" type="number" min="0" step="0.1" label="Employer match %" />
                  </VCol>
                  <VCol v-if="['401(k)', '403(b)'].includes(a.type) && parseFloat(a.employer_match_pct) > 0" cols="12" sm="6">
                    <AppTextField v-model="a.employer_match_limit" type="number" min="0" step="0.01" label="Employer match limit ($)" />
                  </VCol>
                </VRow>
              </div>
              <div class="d-flex justify-space-between mt-4">
                <VBtn variant="text" @click="skip">
                  Skip for now, take me to the dashboard
                </VBtn>
                <VBtn color="primary" size="large" :loading="saving" @click="finish">
                  See My Action Plan →
                </VBtn>
              </div>
            </VCardText>
          </VCard>
        </template>
      </VCol>
    </VRow>
    </VContainer>
  </div>
</template>

<style lang="scss" scoped>
/* Match register/dashboard FreedomStack dark theme */
.onboarding-page {
  --fs-black: #0a0a0a;
  --fs-black-card: #111111;
  --fs-white: #fafaf9;
  --fs-green: #22c55e;
  --fs-gray-300: #d4d4d8;
  --fs-gray-400: #a1a1aa;
  --fs-gray-500: #71717a;
  --fs-gray-600: #52525b;
  min-height: 100vh;
  background: var(--fs-black);
  color: var(--fs-white);
}

.onboarding-page :deep(.v-container),
.onboarding-page :deep(.v-card) {
  color: var(--fs-white);
}

.onboarding-page :deep(.v-card) {
  background: var(--fs-black-card);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.onboarding-page :deep(.v-field),
.onboarding-page :deep(.v-input),
.onboarding-page :deep(.v-field__input),
.onboarding-page :deep(.v-label) {
  color: var(--fs-white);
}

.onboarding-page :deep(.v-field .v-field__input),
.onboarding-page :deep(.v-field .v-label) {
  color: var(--fs-white);
}

.onboarding-page :deep(.text-h4),
.onboarding-page :deep(.text-h5),
.onboarding-page :deep(.text-h6),
.onboarding-page :deep(.text-subtitle-1),
.onboarding-page :deep(.text-body-1),
.onboarding-page :deep(.text-body-2) {
  color: var(--fs-white);
}

.onboarding-page :deep(.text-medium-emphasis),
.onboarding-page :deep(.text-caption) {
  color: var(--fs-gray-300);
}

.onboarding-page :deep(.v-btn) {
  color: var(--fs-white);
}

.onboarding-page :deep(.v-btn--variant-tonal.v-btn--color-primary),
.onboarding-page :deep(.v-btn--variant-flat.v-btn--color-primary) {
  color: var(--fs-black);
  background: var(--fs-green);
}
</style>
