<script setup lang="ts">
/**
 * Live demo calculator — matches freedomstack-landing-page.html
 * Presentational only; authoritative calculations are in backend (PHP/bcmath).
 */
const expenseInput = ref('3,200')

function parseAmount(str: string): number {
  return parseInt(str.replace(/[^0-9]/g, ''), 10) || 0
}

function formatCurrency(num: number): string {
  return '$' + num.toLocaleString('en-US')
}

const monthly = computed(() => parseAmount(expenseInput.value))
const annual = computed(() => monthly.value * 12)

const freedom4 = computed(() => Math.round(annual.value / 0.04))
const freedom3 = computed(() => Math.round(annual.value / 0.03))
const freedom6 = computed(() => Math.round(annual.value / 0.06))

const selectedRate = ref<'3' | '4' | '6'>('4')
const displayFreedom = computed(() => {
  if (selectedRate.value === '3') return freedom3.value
  if (selectedRate.value === '6') return freedom6.value
  return freedom4.value
})

function onInput(e: Event) {
  const target = e.target as HTMLInputElement
  const raw = target.value.replace(/[^0-9]/g, '')
  if (raw) {
    expenseInput.value = parseInt(raw, 10).toLocaleString('en-US')
  }
}
</script>

<template>
  <section
    id="calculator"
    class="math-section reveal"
  >
    <VContainer class="math-container">
      <div class="section-label text-uppercase mb-4">
        // try it right now
      </div>
      <h2 class="text-h3 text-md-h2 text-center font-weight-normal mb-2">
        See your number in real time
      </h2>
      <p class="text-body-large text-medium-emphasis text-center mx-auto mb-8">
        Type your monthly expenses below. Watch the math happen.
      </p>

      <VCard
        variant="outlined"
        class="demo-calculator pa-6 pa-md-8"
      >
        <div class="demo-input-group mb-6">
          <label class="demo-label text-caption text-medium-emphasis d-block mb-2">
            My monthly expenses are roughly...
          </label>
          <div class="d-flex align-center gap-2 flex-wrap">
            <span class="text-h5 text-medium-emphasis">$</span>
            <input
              :value="expenseInput"
              type="text"
              inputmode="numeric"
              class="demo-input flex-grow-1"
              @input="onInput"
            >
            <span class="text-body-2 text-medium-emphasis">/ month</span>
          </div>
        </div>

        <VDivider class="my-6" />

        <div class="demo-result text-center">
          <div class="demo-result-label text-uppercase text-caption text-medium-emphasis mb-2">
            Your Freedom Number
          </div>
          <div class="demo-result-number text-primary mb-2">
            {{ formatCurrency(displayFreedom) }}
          </div>
          <p class="text-body-2 text-medium-emphasis mx-auto mb-6">
            at a <strong class="text-high-emphasis">4% safe withdrawal rate</strong> — the gold standard from 30 years of financial research. This is the amount that, properly invested, covers your life without running out.
          </p>

          <div class="demo-range d-flex flex-wrap justify-center gap-3">
            <VCard
              variant="outlined"
              class="demo-range-card flex-grow-1"
              :class="{ 'range-active': selectedRate === '6' }"
              max-width="180"
              @click="selectedRate = '6'"
            >
              <div class="pa-3 text-center">
                <div class="text-caption text-medium-emphasis mb-1">6% optimistic</div>
                <div class="text-body-1 font-weight-medium">{{ formatCurrency(freedom6) }}</div>
                <div class="text-caption text-disabled">strong returns</div>
              </div>
            </VCard>
            <VCard
              variant="outlined"
              class="demo-range-card flex-grow-1"
              :class="{ 'range-active': selectedRate === '4' }"
              max-width="180"
              @click="selectedRate = '4'"
            >
              <div class="pa-3 text-center">
                <div class="text-caption text-medium-emphasis mb-1">4% safe</div>
                <div class="text-body-1 font-weight-medium">{{ formatCurrency(freedom4) }}</div>
                <div class="text-caption text-disabled">recommended</div>
              </div>
            </VCard>
            <VCard
              variant="outlined"
              class="demo-range-card flex-grow-1"
              :class="{ 'range-active': selectedRate === '3' }"
              max-width="180"
              @click="selectedRate = '3'"
            >
              <div class="pa-3 text-center">
                <div class="text-caption text-medium-emphasis mb-1">3% conservative</div>
                <div class="text-body-1 font-weight-medium">{{ formatCurrency(freedom3) }}</div>
                <div class="text-caption text-disabled">extra cautious</div>
              </div>
            </VCard>
          </div>
        </div>
      </VCard>

      <div class="text-center mt-8">
        <VBtn
          :to="{ path: '/calculator' }"
          color="primary"
          size="large"
        >
          Get my full action plan
          <VIcon
            icon="tabler-arrow-right"
            class="ms-2"
            size="20"
          />
        </VBtn>
      </div>
    </VContainer>
  </section>
</template>

<style lang="scss" scoped>
.math-section {
  padding-block: 6rem 8rem;
}

.math-container {
  max-width: 780px;
}

.section-label {
  font-size: 0.75rem;
  letter-spacing: 0.15em;
  color: var(--fs-green, #22c55e);
  font-family: "JetBrains Mono", ui-monospace, monospace;
}

.demo-calculator {
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.demo-input {
  background: transparent;
  border: none;
  border-bottom: 2px solid rgba(255, 255, 255, 0.15);
  color: var(--fs-white, #fafaf9);
  font-size: 2rem;
  font-weight: 500;
  padding: 0.25rem 0;
  outline: none;
  transition: border-color 0.3s;
  font-family: "JetBrains Mono", ui-monospace, monospace;
  min-width: 120px;
}

.demo-input:focus {
  border-color: var(--fs-green, #22c55e);
}

.demo-result-label {
  letter-spacing: 0.1em;
  font-family: "JetBrains Mono", ui-monospace, monospace;
}

.demo-result-number {
  font-size: clamp(2rem, 5vw, 4rem);
  font-weight: 500;
  font-family: "JetBrains Mono", ui-monospace, monospace;
  letter-spacing: -0.02em;
  color: var(--fs-green, #22c55e);
}

.demo-range-card {
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
}

.demo-range-card.range-active,
.demo-range-card:hover {
  border-color: var(--fs-green, #22c55e) !important;
  background: var(--fs-green-glow, rgba(34, 197, 94, 0.12)) !important;
}

.reveal {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}

.reveal.visible {
  opacity: 1;
  transform: translateY(0);
}
</style>
