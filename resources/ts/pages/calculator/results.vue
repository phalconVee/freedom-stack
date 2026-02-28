<script setup lang="ts">
/**
 * Freedom Number Results — journey §2.3
 * URL: /calculator/results. Usually reached via navigation from calculator with state.
 */
definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

interface ResultState {
  monthly_expenses: number
  freedom_number_at_3_pct: number
  freedom_number_at_4_pct: number
  freedom_number_at_5_pct: number
  freedom_number_at_6_pct: number
  years_to_freedom: number | null
}

const router = useRouter()
const result = ref<ResultState | null>(null)

onMounted(() => {
  const state = history.state as { result?: ResultState } | undefined
  if (state?.result)
    result.value = state.result
})

function formatCents(cents: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(cents / 100)
}

function recalculate() {
  router.push('/calculator')
}
</script>

<template>
  <VContainer class="py-12">
    <template v-if="result">
      <div class="text-center mb-8">
        <h1 class="text-h4 mb-2 text-medium-emphasis">
          Your Freedom Number
        </h1>
        <p class="text-h2 font-weight-bold text-primary mb-2">
          {{ formatCents(result.freedom_number_at_4_pct) }}
        </p>
        <p class="text-body-2 text-medium-emphasis">
          at a 4% safe withdrawal rate
        </p>
      </div>
      <VAlert type="info" variant="tonal" class="mb-6 mx-auto" max-width="600">
        This might feel like a big number. With a solid strategy, people in similar positions typically build this in 15–20 years. Some do it faster. Let's see where you stand.
      </VAlert>
      <VRow justify="center" class="mb-8">
        <VCol cols="12" sm="6" md="4">
          <VCard variant="tonal" color="primary">
            <VCardText class="text-center">
              <div class="text-caption">Conservative (4%)</div>
              <div class="text-h6">{{ formatCents(result.freedom_number_at_4_pct) }}</div>
              <div class="text-caption text-medium-emphasis">Safest — survives market downturns</div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" sm="6" md="4">
          <VCard variant="tonal" color="secondary">
            <VCardText class="text-center">
              <div class="text-caption">Moderate (5%)</div>
              <div class="text-h6">{{ formatCents(result.freedom_number_at_5_pct) }}</div>
              <div class="text-caption text-medium-emphasis">Balanced — typical diversified portfolio</div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" sm="6" md="4">
          <VCard variant="tonal" color="success">
            <VCardText class="text-center">
              <div class="text-caption">Optimistic (6%)</div>
              <div class="text-h6">{{ formatCents(result.freedom_number_at_6_pct) }}</div>
              <div class="text-caption text-medium-emphasis">Assumes strong returns</div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
      <div class="d-flex flex-wrap justify-center gap-4">
        <VBtn color="primary" size="large" :to="'/register'">
          Get Your Personalized Action Plan →
        </VBtn>
        <VBtn variant="outlined" @click="recalculate">
          Recalculate with different numbers
        </VBtn>
      </div>
    </template>
    <template v-else>
      <div class="text-center">
        <p class="text-body-1 text-medium-emphasis mb-4">
          No results to show. Calculate your Freedom Number first.
        </p>
        <VBtn color="primary" :to="'/calculator'">
          Calculate My Freedom Number
        </VBtn>
      </div>
    </template>
  </VContainer>
</template>
