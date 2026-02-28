<script setup lang="ts">
/**
 * Progress — FREEDOMSTACK_USER_JOURNEY.md §6.8
 * URL: /progress. Monthly tracking, net worth over time.
 */
definePage({
  meta: {
    layout: 'dashboard-shell',
  },
})

const summary = ref<any>(null)
const loading = ref(true)

function formatCents(cents: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(cents / 100)
}

onMounted(async () => {
  try {
    const res = await $api<{ data: any }>('/progress/summary')
    summary.value = res.data
  } catch (_) {}
  finally {
    loading.value = false
  }
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h1 class="text-h3 mb-4">
        Progress
      </h1>
      <p class="text-body-1 text-medium-emphasis mb-6">
        Your financial snapshot and progress toward your Freedom Number.
      </p>
    </VCol>
    <VCol v-if="loading" cols="12">
      <VProgressLinear indeterminate color="primary" />
    </VCol>
    <template v-else-if="summary">
      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="primary">
          <VCardText>
            <div class="text-caption">
              Net worth
            </div>
            <div class="text-h6">
              {{ formatCents(summary.net_worth) }}
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="error">
          <VCardText>
            <div class="text-caption">
              Total debt
            </div>
            <div class="text-h6">
              {{ formatCents(summary.total_debt) }}
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="success">
          <VCardText>
            <div class="text-caption">
              Freedom number (4%)
            </div>
            <div class="text-h6">
              {{ formatCents(summary.freedom_number) }}
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard variant="tonal" color="info">
          <VCardText>
            <div class="text-caption">
              Freedom % achieved
            </div>
            <div class="text-h6">
              {{ summary.freedom_pct_achieved != null ? summary.freedom_pct_achieved + '%' : '—' }}
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </template>
    <VCol v-else cols="12">
      <VAlert type="info">
        Log your first check-in to start tracking your journey.
      </VAlert>
    </VCol>
  </VRow>
</template>
