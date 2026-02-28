<script setup lang="ts">
/**
 * Calculator flow — public 3-step flow; action plan is after register/onboarding.
 * Screen 1: Expenses (Simple/Detailed). Screen 2: Lifestyle. Screen 3: Reveal → CTA to register.
 */
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'authenticated-or-blank',
    public: true,
  },
})

const step = ref(1)
const mode = ref<'simple' | 'detailed'>('simple')
const simpleExpense = ref('3,200')
const selectedMultiplier = ref(1)

const categoryAmounts = reactive<Record<string, string>>({
  housing: '1,500',
  transportation: '400',
  food: '500',
  insurance: '200',
  healthcare: '150',
  utilities: '200',
  debt: '500',
  personal: '150',
  entertainment: '200',
  education: '0',
  other: '0',
})

const categories = [
  { key: 'housing', icon: '🏠', name: 'Housing', essential: true },
  { key: 'transportation', icon: '🚗', name: 'Transportation', essential: true },
  { key: 'food', icon: '🛒', name: 'Food & Groceries', essential: true },
  { key: 'insurance', icon: '🛡️', name: 'Insurance', essential: true },
  { key: 'healthcare', icon: '⚕️', name: 'Healthcare', essential: true },
  { key: 'utilities', icon: '💡', name: 'Utilities', essential: true },
  { key: 'debt', icon: '💳', name: 'Debt Payments', essential: false },
  { key: 'personal', icon: '🛍️', name: 'Personal & Shopping', essential: false },
  { key: 'entertainment', icon: '🎬', name: 'Entertainment & Dining', essential: false },
  { key: 'education', icon: '📚', name: 'Education', essential: false },
  { key: 'other', icon: '➕', name: 'Other', essential: false },
]

function parseNum(str: string): number {
  return parseInt(String(str).replace(/[^0-9]/g, ''), 10) || 0
}

function fmt(n: number): string {
  return '$' + Math.round(n).toLocaleString('en-US')
}

const monthlyExpenses = computed(() => {
  if (mode.value === 'simple') return parseNum(simpleExpense.value)
  return (Object.values(categoryAmounts) as string[]).reduce((sum, val) => sum + parseNum(val), 0)
})

const categoryTotal = computed(() => monthlyExpenses.value)

const lifestyleAmounts = computed(() => ({
  basic: monthlyExpenses.value * 0.77,
  comfort: monthlyExpenses.value,
  full: monthlyExpenses.value * 1.2,
}))

const adjustedMonthly = computed(() => monthlyExpenses.value * selectedMultiplier.value)
const annualAdjusted = computed(() => adjustedMonthly.value * 12)
const freedom4 = computed(() => Math.round(annualAdjusted.value / 0.04))
const freedom3 = computed(() => Math.round(annualAdjusted.value / 0.03))
const freedom6 = computed(() => Math.round(annualAdjusted.value / 0.06))

const progressLabel = computed(() => {
  const labels: Record<number, string> = {
    1: 'Step 1 of 3',
    2: 'Step 2 of 3',
    3: 'Your number',
  }
  return labels[step.value] || ''
})

function setMode(m: 'simple' | 'detailed') {
  mode.value = m
}

function selectLifestyle(mult: number) {
  selectedMultiplier.value = mult
}

const revealKey = ref(0)

function goToScreen(num: 1 | 2 | 3) {
  step.value = num
  if (num === 3) {
    revealKey.value += 1
    // Persist calculator result for register page (sessionStorage)
    try {
      sessionStorage.setItem('fs_freedom_number', String(freedom4.value))
      sessionStorage.setItem('fs_monthly_expenses', String(Math.round(adjustedMonthly.value)))
      sessionStorage.setItem('fs_withdrawal_rate', '0.04')
    } catch {
      // ignore
    }
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Format simple input on type
function onSimpleInput(e: Event) {
  const t = e.target as HTMLInputElement
  const raw = t.value.replace(/[^0-9]/g, '')
  if (raw) simpleExpense.value = parseInt(raw, 10).toLocaleString('en-US')
}

function onCatInput(key: string, e: Event) {
  const t = e.target as HTMLInputElement
  const raw = t.value.replace(/[^0-9]/g, '')
  if (raw) categoryAmounts[key] = parseInt(raw, 10).toLocaleString('en-US')
}
</script>

<template>
  <div class="calculator-flow-page">
    <!-- Nav (matches prototype) -->
    <nav class="calc-nav">
      <RouterLink to="/" class="calc-logo">
        <div class="calc-logo-icon">F</div>
        {{ themeConfig.app.title }}
      </RouterLink>
      <div class="calc-nav-right">
        <RouterLink to="/login">Log in</RouterLink>
      </div>
    </nav>

    <div class="flow-container">
      <!-- Progress bar -->
      <div v-show="step < 3" class="progress-bar">
        <div
          v-for="i in 3"
          :key="i"
          class="progress-step"
          :class="{
            complete: step > i,
            current: step === i,
          }"
        >
          <div class="fill" />
        </div>
        <span class="progress-label">{{ progressLabel }}</span>
      </div>

      <!-- Screen 1: Expenses -->
      <section v-show="step === 1" class="screen active screen-expenses">
        <h1 class="screen-title">What does your life cost each month?</h1>
        <p class="screen-subtitle">
          Include everything — rent, groceries, loans, subscriptions, the coffee habit you're not ready to talk about. No judgment.
        </p>

        <div class="mode-toggle">
          <button
            type="button"
            class="mode-btn"
            :class="{ active: mode === 'simple' }"
            @click="setMode('simple')"
          >
            Simple
          </button>
          <button
            type="button"
            class="mode-btn"
            :class="{ active: mode === 'detailed' }"
            @click="setMode('detailed')"
          >
            Detailed
          </button>
        </div>

        <!-- Simple mode -->
        <div v-show="mode === 'simple'" class="simple-input-wrap">
          <span class="dollar-sign">$</span>
          <input
            :value="simpleExpense"
            type="text"
            inputmode="numeric"
            class="big-input"
            @input="onSimpleInput"
          >
        </div>
        <p v-show="mode === 'simple'" class="input-helper">
          per month, roughly. You can get specific later.
        </p>

        <!-- Detailed mode -->
        <div v-show="mode === 'detailed'" class="category-grid visible">
          <div
            v-for="cat in categories"
            :key="cat.key"
            class="category-row"
          >
            <span class="cat-icon">{{ cat.icon }}</span>
            <span class="cat-name">
              {{ cat.name }}
              <span v-if="cat.essential" class="essential-badge">essential</span>
            </span>
            <div class="cat-input-wrap">
              <span class="cat-dollar">$</span>
              <input
                :value="categoryAmounts[cat.key]"
                type="text"
                class="cat-input"
                @input="onCatInput(cat.key, $event)"
              >
            </div>
          </div>
          <div class="category-total">
            <span class="label">Monthly total</span>
            <span class="amount">{{ fmt(categoryTotal) }}</span>
          </div>
        </div>

        <button
          type="button"
          class="btn-next"
          @click="goToScreen(2)"
        >
          Next: Choose your lifestyle
          <span class="arrow">→</span>
        </button>
      </section>

      <!-- Screen 2: Lifestyle -->
      <section v-show="step === 2" class="screen active screen-lifestyle">
        <h1 class="screen-title">What does freedom look like for you?</h1>
        <p class="screen-subtitle">
          This determines which expenses your Freedom Number needs to cover.
        </p>

        <div class="lifestyle-cards">
          <div
            class="lifestyle-card"
            :class="{ selected: selectedMultiplier === 0.77 }"
            @click="selectLifestyle(0.77)"
          >
            <div class="icon">🏡</div>
            <h3>Just the basics</h3>
            <p>Cover essential expenses only — roof, food, healthcare, bills.</p>
            <div class="calc-amount">{{ fmt(lifestyleAmounts.basic) }} / mo</div>
          </div>
          <div
            class="lifestyle-card"
            :class="{ selected: selectedMultiplier === 1 }"
            @click="selectLifestyle(1)"
          >
            <div class="icon">✨</div>
            <h3>Comfortable</h3>
            <p>Cover everything you spend today, including the fun stuff.</p>
            <div class="calc-amount">{{ fmt(lifestyleAmounts.comfort) }} / mo</div>
          </div>
          <div
            class="lifestyle-card"
            :class="{ selected: selectedMultiplier === 1.2 }"
            @click="selectLifestyle(1.2)"
          >
            <div class="icon">🌎</div>
            <h3>Full freedom</h3>
            <p>Current expenses plus 20% for travel, hobbies, and surprises.</p>
            <div class="calc-amount">{{ fmt(lifestyleAmounts.full) }} / mo</div>
          </div>
        </div>

        <button
          type="button"
          class="btn-primary-calc"
          @click="goToScreen(3)"
        >
          Show my Freedom Number
          <span class="arrow">→</span>
        </button>
      </section>

      <!-- Screen 3: The Reveal (key forces re-mount so animations run each time) -->
      <section v-show="step === 3" class="screen active screen-reveal">
        <div :key="'reveal-' + revealKey" class="results-container">
          <div class="results-glow" />
          <div class="results-pre-label">Your Freedom Number</div>
          <div class="freedom-number-display">{{ fmt(freedom4) }}</div>
          <div class="rate-badge">
            <span class="dot" />
            at 4% safe withdrawal rate
          </div>
          <div class="results-context">
            <p>
              That's the amount that, properly invested, generates <strong>{{ fmt(adjustedMonthly) }}/month</strong> — enough to cover your life, indefinitely. It might feel like a big number. <em>It's not as far away as you think.</em>
            </p>
          </div>
          <div class="range-strip">
            <div class="range-card">
              <div class="rate-label">6% Optimistic</div>
              <div class="range-amount">{{ fmt(freedom6) }}</div>
              <div class="range-note">Strong market returns</div>
            </div>
            <div class="range-card highlight">
              <div class="rate-label">4% Safe · Recommended</div>
              <div class="range-amount">{{ fmt(freedom4) }}</div>
              <div class="range-note">Trinity Study standard</div>
            </div>
            <div class="range-card">
              <div class="rate-label">3% Conservative</div>
              <div class="range-amount">{{ fmt(freedom3) }}</div>
              <div class="range-note">Extra cushion</div>
            </div>
          </div>
          <div class="results-divider" />
          <div class="results-cta">
            <h3>Now — how do you <em>get there?</em></h3>
            <p class="cta-sub">
              Create an account and complete a short onboarding. We'll build a step-by-step action plan showing exactly how each move speeds up your timeline.
            </p>
            <div class="results-actions">
              <RouterLink to="/register" class="btn-primary-calc">
                Create account to get your action plan
                <span class="arrow">→</span>
              </RouterLink>
              <button
                type="button"
                class="btn-secondary"
                @click="goToScreen(1)"
              >
                ← Recalculate
              </button>
              <span class="fine-print">Free. No credit card. Takes about 2 minutes.</span>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style lang="scss" scoped>
/* Uses same palette as landing — ensure vars available on this page */
.calculator-flow-page {
  --fs-black: #0a0a0a;
  --fs-white: #fafaf9;
  --fs-green: #22c55e;
  --fs-green-glow: rgba(34, 197, 94, 0.12);
  --fs-green-glow-strong: rgba(34, 197, 94, 0.25);
  --fs-gray-300: #d4d4d8;
  --fs-gray-400: #a1a1aa;
  --fs-gray-500: #71717a;
  --fs-gray-600: #52525b;
  --fs-amber: #f59e0b;
  min-height: 100vh;
  background: var(--fs-black);
  color: var(--fs-white);
  font-family: "DM Sans", system-ui, sans-serif;
}

.calc-nav {
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 100;
  padding: 1.25rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(10, 10, 10, 0.85);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.calc-logo {
  font-weight: 700;
  font-size: 1.15rem;
  letter-spacing: -0.02em;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: var(--fs-white);
}

.calc-logo-icon {
  width: 28px;
  height: 28px;
  background: var(--fs-green);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  color: var(--fs-black);
  font-weight: 700;
}

.calc-nav-right a {
  color: var(--fs-gray-400);
  text-decoration: none;
  font-size: 0.9rem;
}

.calc-nav-right a:hover {
  color: var(--fs-white);
}

.flow-container {
  max-width: 720px;
  margin: 0 auto;
  padding: 7rem 2rem 4rem;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.screen {
  display: none;
  animation: screenIn 0.6s ease both;
}

.screen.active {
  display: block;
}

@keyframes screenIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Progress bar */
.progress-bar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 3rem;
}

.progress-step {
  height: 3px;
  flex: 1;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 100px;
  overflow: hidden;
  transition: all 0.5s;
}

.progress-step .fill {
  height: 100%;
  width: 0%;
  background: var(--fs-green);
  border-radius: 100px;
  transition: width 0.6s ease;
}

.progress-step.complete .fill {
  width: 100%;
}

.progress-step.current .fill {
  width: 50%;
}

.progress-label {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.7rem;
  color: var(--fs-gray-600);
  letter-spacing: 0.05em;
  white-space: nowrap;
}

/* Screen titles */
.screen-title {
  font-family: "Instrument Serif", Georgia, serif;
  font-size: clamp(1.8rem, 4vw, 2.8rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
  margin-bottom: 0.75rem;
}

.screen-subtitle {
  color: var(--fs-gray-400);
  font-size: 1.05rem;
  margin-bottom: 2.5rem;
  line-height: 1.6;
}

.text-green {
  color: var(--fs-green);
}

/* Screen 1: Mode toggle */
.mode-toggle {
  display: inline-flex;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  padding: 3px;
  margin-bottom: 2rem;
}

.mode-btn {
  padding: 0.5rem 1.25rem;
  font-size: 0.85rem;
  border: none;
  background: transparent;
  color: var(--fs-gray-400);
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.25s;
}

.mode-btn.active {
  background: rgba(255, 255, 255, 0.1);
  color: var(--fs-white);
}

.dollar-sign {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 2.5rem;
  color: var(--fs-gray-500);
}

.big-input {
  background: transparent;
  border: none;
  border-bottom: 2px solid rgba(255, 255, 255, 0.12);
  color: var(--fs-white);
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: clamp(2.5rem, 5vw, 3.5rem);
  font-weight: 500;
  width: 280px;
  padding: 0.25rem 0;
  outline: none;
  transition: border-color 0.3s;
  letter-spacing: -0.02em;
}

.big-input:focus {
  border-color: var(--fs-green);
}

.simple-input-wrap {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.input-helper {
  font-size: 0.85rem;
  color: var(--fs-gray-600);
  margin-top: 0.25rem;
}

.category-grid {
  display: none;
}

.category-grid.visible {
  display: block;
}

.category-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.85rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.cat-icon {
  font-size: 1.25rem;
  width: 36px;
  text-align: center;
}

.cat-name {
  flex: 1;
  font-size: 0.9rem;
}

.essential-badge {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.6rem;
  color: #166534;
  background: var(--fs-green-glow);
  padding: 0.1rem 0.4rem;
  border-radius: 3px;
  margin-left: 0.5rem;
  vertical-align: middle;
}

.cat-input-wrap {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.cat-dollar {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.85rem;
  color: var(--fs-gray-600);
}

.cat-input {
  background: transparent;
  border: none;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--fs-white);
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 1rem;
  width: 90px;
  padding: 0.25rem 0;
  outline: none;
  text-align: right;
}

.cat-input:focus {
  border-color: var(--fs-green);
}

.category-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
  padding: 1rem 0;
  border-top: 2px solid rgba(255, 255, 255, 0.08);
}

.category-total .label {
  font-size: 0.85rem;
  color: var(--fs-gray-400);
}

.category-total .amount {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 1.5rem;
  font-weight: 500;
  color: var(--fs-green);
}

/* Buttons */
.btn-next {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--fs-white);
  color: var(--fs-black);
  padding: 0.85rem 2rem;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.25s;
  margin-top: 2rem;
}

.btn-next:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(255, 255, 255, 0.1);
}

.btn-primary-calc {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--fs-green);
  color: var(--fs-white);
  padding: 1rem 2.25rem;
  border-radius: 12px;
  font-size: 1.05rem;
  font-weight: 600;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.25s;
  margin-top: 1rem;
}

.btn-primary-calc:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(34, 197, 94, 0.3);
}

.btn-secondary {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--fs-gray-400);
  padding: 0.75rem 1.75rem;
  border-radius: 10px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.25s;
  text-decoration: none;
  margin-top: 0.5rem;
}

.btn-secondary:hover {
  border-color: rgba(255, 255, 255, 0.2);
  color: var(--fs-white);
}

.fine-print {
  font-size: 0.78rem;
  color: var(--fs-gray-600);
  margin-top: 0.5rem;
  display: block;
}

/* Screen 2: Lifestyle cards */
.lifestyle-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.lifestyle-card {
  background: rgba(255, 255, 255, 0.02);
  border: 2px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 1.75rem 1.25rem;
  cursor: pointer;
  transition: all 0.25s;
  text-align: center;
}

.lifestyle-card:hover {
  border-color: rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.04);
}

.lifestyle-card.selected {
  border-color: var(--fs-green);
  background: var(--fs-green-glow);
}

.lifestyle-card .icon {
  font-size: 2rem;
  margin-bottom: 0.75rem;
}

.lifestyle-card h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.lifestyle-card p {
  font-size: 0.8rem;
  color: var(--fs-gray-400);
  line-height: 1.5;
}

.lifestyle-card .calc-amount {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.85rem;
  color: var(--fs-green);
  margin-top: 0.75rem;
  opacity: 0.8;
}

/* Screen 3: Reveal */
.results-container {
  text-align: center;
  position: relative;
}

.results-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -60%);
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, var(--fs-green-glow-strong) 0%, transparent 65%);
  pointer-events: none;
  opacity: 0;
  animation: glowIn 1.5s ease 0.8s forwards;
}

@keyframes glowIn {
  to { opacity: 1; }
}

.results-pre-label {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: var(--fs-gray-500);
  margin-bottom: 1.5rem;
  opacity: 0;
  animation: fadeInUp 0.5s ease 0.2s forwards;
}

.freedom-number-display {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: clamp(3.5rem, 10vw, 6.5rem);
  font-weight: 600;
  color: var(--fs-green);
  letter-spacing: -0.03em;
  position: relative;
  z-index: 2;
  opacity: 0;
  animation: numberReveal 0.8s ease 0.5s forwards;
  margin-bottom: 0.25rem;
}

@keyframes numberReveal {
  0% { opacity: 0; transform: scale(0.8) translateY(20px); filter: blur(10px); }
  60% { opacity: 1; transform: scale(1.02) translateY(-4px); filter: blur(0); }
  100% { opacity: 1; transform: scale(1) translateY(0); filter: blur(0); }
}

.rate-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.8rem;
  color: var(--fs-gray-400);
  padding: 0.35rem 0.85rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 100px;
  margin-bottom: 2.5rem;
  opacity: 0;
  animation: fadeInUp 0.5s ease 1.1s forwards;
}

.rate-badge .dot {
  width: 6px;
  height: 6px;
  background: var(--fs-green);
  border-radius: 50%;
}

.results-context {
  max-width: 500px;
  margin: 0 auto 2.5rem;
  opacity: 0;
  animation: fadeInUp 0.6s ease 1.4s forwards;
}

.results-context p {
  font-size: 1.1rem;
  color: var(--fs-gray-300);
  line-height: 1.7;
}

.results-context strong {
  color: var(--fs-white);
}

.results-context em {
  color: var(--fs-green);
  font-style: normal;
  font-weight: 600;
}

.range-strip {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  margin-bottom: 3rem;
  opacity: 0;
  animation: fadeInUp 0.6s ease 1.8s forwards;
}

.range-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 1.15rem;
}

.range-card.highlight {
  border-color: rgba(34, 197, 94, 0.3);
  background: rgba(34, 197, 94, 0.06);
}

.range-card .rate-label {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--fs-gray-500);
  margin-bottom: 0.25rem;
}

.range-card .range-amount {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 1.3rem;
  font-weight: 500;
}

.range-card .range-note {
  font-size: 0.7rem;
  color: var(--fs-gray-600);
  margin-top: 0.15rem;
}

.range-card.highlight .range-amount {
  color: var(--fs-green);
}

.results-divider {
  width: 60px;
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 0 auto 2.5rem;
  opacity: 0;
  animation: fadeInUp 0.5s ease 2.1s forwards;
}

.results-cta {
  opacity: 0;
  animation: fadeInUp 0.6s ease 2.3s forwards;
}

.results-cta h3 {
  font-family: "Instrument Serif", Georgia, serif;
  font-size: 1.6rem;
  margin-bottom: 0.75rem;
  letter-spacing: -0.01em;
}

.results-cta h3 em {
  font-style: italic;
  color: var(--fs-green);
}

.results-cta .cta-sub {
  color: var(--fs-gray-400);
  font-size: 0.95rem;
  margin-bottom: 2rem;
  max-width: 420px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

.results-actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Screen 4: Action plan */
.plan-preview {
  text-align: left;
}

.plan-header {
  text-align: center;
  margin-bottom: 3rem;
}

.plan-header .screen-subtitle {
  margin-bottom: 0;
}

.gap-visual {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  opacity: 0;
  animation: fadeInUp 0.5s ease 0.2s forwards;
}

.gap-endpoints {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.5rem;
}

.gap-point {
  text-align: center;
}

.gap-point .gap-label {
  font-size: 0.75rem;
  color: var(--fs-gray-500);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-family: "JetBrains Mono", ui-monospace, monospace;
  margin-bottom: 0.35rem;
}

.gap-point .gap-amount {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 1.5rem;
  font-weight: 500;
}

.gap-point .gap-amount.current {
  color: var(--fs-amber);
}

.gap-point .gap-amount.target {
  color: var(--fs-green);
}

.gap-arrow {
  color: var(--fs-gray-600);
  font-size: 1.5rem;
  padding-bottom: 0.5rem;
}

.gap-bar-track {
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 100px;
  overflow: hidden;
  margin-bottom: 1rem;
}

.gap-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--fs-amber), var(--fs-green));
  border-radius: 100px;
  width: 0%;
  animation: barFill 1.5s ease 0.8s forwards;
}

@keyframes barFill {
  to { width: var(--fill-pct, 3%); }
}

.gap-stat-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
}

.gap-stat-row .label {
  color: var(--fs-gray-500);
}

.gap-stat-row .value {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  color: var(--fs-white);
}

.ai-loading {
  text-align: center;
  padding: 3rem 1rem;
  opacity: 0;
  animation: fadeInUp 0.5s ease 0.6s forwards;
}

.ai-loading-spinner {
  width: 48px;
  height: 48px;
  border: 3px solid rgba(255, 255, 255, 0.06);
  border-top-color: var(--fs-green);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1.5rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.ai-loading h3 {
  font-family: "Instrument Serif", Georgia, serif;
  font-size: 1.3rem;
  margin-bottom: 1.5rem;
}

.ai-steps {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-width: 300px;
  margin: 0 auto;
}

.ai-step {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.85rem;
  color: var(--fs-gray-600);
  transition: all 0.4s;
}

.ai-step.active {
  color: var(--fs-gray-300);
}

.ai-step.done {
  color: var(--fs-green);
}

.ai-step-dot {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  transition: all 0.4s;
  flex-shrink: 0;
}

.ai-step.active .ai-step-dot {
  border-color: var(--fs-green);
  background: var(--fs-green-glow);
}

.ai-step.done .ai-step-dot {
  border-color: var(--fs-green);
  background: var(--fs-green);
  color: var(--fs-black);
}

.plan-results {
  display: none;
}

.plan-results.visible {
  display: block;
  animation: screenIn 0.6s ease both;
}

.plan-summary-card {
  background: rgba(34, 197, 94, 0.06);
  border: 1px solid rgba(34, 197, 94, 0.15);
  border-radius: 14px;
  padding: 1.75rem;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
  color: var(--fs-gray-300);
  line-height: 1.7;
}

.plan-summary-card strong {
  color: var(--fs-white);
}

.rec-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 1.75rem;
  margin-bottom: 1rem;
  transition: all 0.3s;
  opacity: 0;
  transform: translateY(15px);
}

.rec-card.visible {
  opacity: 1;
  transform: translateY(0);
}

.rec-card:hover {
  border-color: rgba(255, 255, 255, 0.12);
}

.rec-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.rec-priority {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: var(--fs-gray-500);
}

.rec-impact {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.8rem;
  color: var(--fs-green);
  background: var(--fs-green-glow);
  padding: 0.2rem 0.7rem;
  border-radius: 6px;
  white-space: nowrap;
}

.rec-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.rec-description {
  font-size: 0.88rem;
  color: var(--fs-gray-400);
  line-height: 1.65;
  margin-bottom: 1rem;
}

.rec-actions {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.rec-action {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.82rem;
  color: var(--fs-gray-400);
}

.rec-action .checkbox {
  width: 16px;
  height: 16px;
  border: 1.5px solid rgba(255, 255, 255, 0.15);
  border-radius: 4px;
  flex-shrink: 0;
  margin-top: 2px;
}

.rec-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

.confidence.high {
  color: var(--fs-green);
}

.confidence.medium {
  color: var(--fs-amber);
}

.confidence {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.toggle-lever {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: var(--fs-gray-400);
  cursor: pointer;
}

.toggle-switch {
  width: 36px;
  height: 20px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 100px;
  position: relative;
  transition: background 0.3s;
}

.toggle-switch::after {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  background: var(--fs-white);
  border-radius: 50%;
  top: 2px;
  left: 2px;
  transition: transform 0.3s;
}

.toggle-lever.active .toggle-switch {
  background: var(--fs-green);
}

.toggle-lever.active .toggle-switch::after {
  transform: translateX(16px);
}

.timeline-panel {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 1.75rem;
  margin-top: 2rem;
  text-align: center;
  opacity: 0;
  transform: translateY(15px);
}

.timeline-panel.visible {
  opacity: 1;
  transform: translateY(0);
  transition: all 0.5s ease;
}

.timeline-label {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: var(--fs-gray-500);
  margin-bottom: 0.5rem;
}

.timeline-before {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 1rem;
  color: var(--fs-gray-500);
  text-decoration: line-through;
  margin-bottom: 0.15rem;
}

.timeline-after {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 2.2rem;
  font-weight: 600;
  color: var(--fs-green);
  letter-spacing: -0.02em;
}

.timeline-saved {
  font-size: 0.85rem;
  color: var(--fs-gray-400);
  margin-top: 0.5rem;
}

.timeline-saved strong {
  color: var(--fs-green);
}

.plan-final-cta {
  text-align: center;
  margin-top: 2.5rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  opacity: 0;
  transform: translateY(15px);
}

.plan-final-cta.visible {
  opacity: 1;
  transform: translateY(0);
  transition: all 0.5s ease;
}

.plan-final-cta h3 {
  font-family: "Instrument Serif", Georgia, serif;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.plan-final-cta p {
  color: var(--fs-gray-400);
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
  .flow-container {
    padding: 6rem 1.25rem 3rem;
  }

  .lifestyle-cards {
    grid-template-columns: 1fr;
  }

  .range-strip {
    grid-template-columns: 1fr;
  }

  .big-input {
    width: 180px;
    font-size: 2.5rem;
  }

  .gap-endpoints {
    flex-direction: column;
    gap: 1rem;
    align-items: center;
  }

  .gap-arrow {
    transform: rotate(90deg);
  }

  .rec-header {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
