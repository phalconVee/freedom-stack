<script setup lang="ts">
/**
 * How It Works — 3 steps. Matches freedomstack-landing-page.html
 */
const steps = [
  {
    number: '01',
    title: 'Tell us what life costs',
    desc: 'Enter your monthly expenses — as a lump sum or category by category. Rent, food, loans, Netflix, all of it. No judgment, just numbers.',
    time: '⏱ 60 seconds',
  },
  {
    number: '02',
    title: 'See your Freedom Number',
    desc: 'We calculate the exact principal you\'d need invested to cover your expenses passively — at conservative, moderate, and optimistic return rates. This is your finish line.',
    time: '⚡ instant',
  },
  {
    number: '03',
    title: 'Get your action plan',
    desc: 'Share your income, debts, and savings — and our AI builds a personalized strategy. Every recommendation shows exactly how many years it shaves off your timeline.',
    time: '🤖 AI-powered',
  },
]
</script>

<template>
  <section
    id="how"
    class="how-it-works-section reveal"
  >
    <VContainer class="how-container">
      <h2 class="text-h3 text-md-h2 text-center mb-12 font-weight-normal">
        Three steps. One plan.
      </h2>
      <VRow>
        <VCol
          v-for="(step, index) in steps"
          :key="index"
          cols="12"
          md="4"
        >
          <VCard
            variant="outlined"
            class="step-card pa-6 h-100"
          >
            <div class="step-number text-primary mb-4">
              {{ step.number }}
            </div>
            <h3 class="text-h5 font-weight-medium mb-2">
              {{ step.title }}
            </h3>
            <p class="text-body-2 text-medium-emphasis mb-4">
              {{ step.desc }}
            </p>
            <VChip
              size="x-small"
              variant="tonal"
              color="default"
              class="step-time"
            >
              {{ step.time }}
            </VChip>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </section>
</template>

<style lang="scss" scoped>
.how-it-works-section {
  padding-block: 6rem 8rem;
}

.how-container {
  max-width: 1100px;
}

.step-number {
  font-size: 0.75rem;
  letter-spacing: 0.1em;
  font-family: "JetBrains Mono", ui-monospace, monospace;
}

.step-card {
  border-radius: 16px;
  transition: all 0.3s;
}

.step-card:hover {
  transform: translateY(-4px);
}

.step-time {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.75rem;
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
