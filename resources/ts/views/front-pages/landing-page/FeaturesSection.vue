<script setup lang="ts">
/**
 * Features grid — matches freedomstack-landing-page.html
 */
const features = [
  {
    icon: 'tabler-robot',
    title: 'AI-powered action plan',
    desc: 'Not generic advice. A prioritized strategy built from your actual numbers — debts, income, investments, all of it. Each recommendation shows exactly how it moves your timeline.',
    tag: 'Personalized to you',
  },
  {
    icon: 'tabler-bolt',
    title: 'Interactive lever toggling',
    desc: 'Toggle recommendations on and off. Watch your freedom timeline shift in real-time. See the difference between paying off debt first vs. maxing your 401K — instantly.',
    tag: 'Real-time',
  },
  {
    icon: 'tabler-chart-bar',
    title: 'Debt payoff optimizer',
    desc: 'See avalanche vs. snowball strategies side by side with your actual debts. Know exactly when you\'ll be debt-free and how much interest you\'ll save with each approach.',
    tag: 'Avalanche & snowball',
  },
  {
    icon: 'tabler-trending-up',
    title: 'Progress tracking',
    desc: 'Monthly check-ins that take 60 seconds. Track net worth, debt reduction, and freedom percentage over time. The dashboard that motivates you to keep going.',
    tag: '60-second updates',
  },
  {
    icon: 'tabler-target',
    title: '401K & retirement optimization',
    desc: 'Are you leaving free money on the table with your employer match? We\'ll tell you exactly how much — and show the optimal contribution order across all your accounts.',
    tag: 'Don\'t miss free money',
  },
  {
    icon: 'tabler-lock',
    title: 'Your data stays yours',
    desc: 'No account linking required. No selling your data. No ads. Ever. We strip all personally identifiable information before any AI processing. You\'re the customer, not the product.',
    tag: 'Privacy-first',
  },
]
</script>

<template>
  <section class="features-section reveal">
    <VContainer class="features-container">
      <h2 class="text-h3 text-md-h2 text-center font-weight-normal mb-2">
        More than a calculator
      </h2>
      <p class="text-body-large text-medium-emphasis text-center mb-12">
        Know your number, then build the bridge to get there.
      </p>
      <VRow>
        <VCol
          v-for="(f, i) in features"
          :key="i"
          cols="12"
          md="6"
        >
          <VCard
            variant="outlined"
            class="feature-card pa-5 h-100"
          >
            <VIcon
              :icon="f.icon"
              size="32"
              class="mb-3"
            />
            <h3 class="text-h6 font-weight-semibold mb-2">
              {{ f.title }}
            </h3>
            <p class="text-body-2 text-medium-emphasis mb-3">
              {{ f.desc }}
            </p>
            <VChip
              size="x-small"
              color="primary"
              variant="tonal"
              class="feature-tag"
            >
              {{ f.tag }}
            </VChip>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </section>
</template>

<style lang="scss" scoped>
.features-section {
  padding-block: 6rem 8rem;
}

.features-container {
  max-width: 1100px;
}

.feature-card {
  border-radius: 16px;
  transition: all 0.3s;
}

.feature-card:hover {
  transform: translateY(-2px);
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
