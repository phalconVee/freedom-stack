<script setup lang="ts">
/**
 * Gap screen — FREEDOMSTACK_USER_JOURNEY.md §3.3
 * Transition between onboarding and AI action plan. Shows net worth vs freedom number, then generates recommendations.
 */
definePage({
  meta: {
    layout: 'blank',
  },
})

const router = useRouter()
const summary = ref<{
  net_worth?: number
  freedom_number?: number
  freedom_pct_achieved?: number
} | null>(null)
const loading = ref(true)
const generating = ref(true)
const messageIndex = ref(0)
const showDashboardOption = ref(false)

const messages = [
  'Analyzing your profile...',
  'Optimizing contributions...',
  'Building your plan...',
]

function formatCents(cents: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(cents / 100)
}

onMounted(async () => {
  try {
    const res = await $api<{ data: any }>('/progress/summary')
    summary.value = res.data
  } catch (_) {
    summary.value = null
    loading.value = false
    generating.value = false
    showDashboardOption.value = true
    return
  } finally {
    loading.value = false
  }

  // Trigger AI recommendation generation
  let jobId: number | null = null
  try {
    const gen = await $api<{ id?: number; status?: string }>('/recommendations/generate', { method: 'POST' })
    if (gen.id && gen.status === 'pending')
      jobId = gen.id
    else if (gen.status === 'completed')
      generating.value = false
  } catch (_) {
    generating.value = false
    showDashboardOption.value = true
    return
  }

  if (jobId) {
    const start = Date.now()
    const poll = async () => {
      if (Date.now() - start > 15000) {
        showDashboardOption.value = true
        generating.value = false
        return
      }
      try {
        const statusRes = await $api<{ status: string }>(`/recommendations/${jobId}/status`)
        if (statusRes.status === 'completed') {
          generating.value = false
          await router.replace('/action-plan')
          return
        }
        if (statusRes.status === 'failed') {
          generating.value = false
          showDashboardOption.value = true
          return
        }
      } catch (_) {
        generating.value = false
        showDashboardOption.value = true
        return
      }
      setTimeout(poll, 2000)
    }
    setTimeout(poll, 2500)
  }
  const messageInterval = setInterval(() => {
    messageIndex.value = (messageIndex.value + 1) % messages.length
  }, 3000)
  onUnmounted(() => clearInterval(messageInterval))
})
</script>

<template>
  <div class="gap-analysis-page">
    <VContainer class="py-12">
      <div class="text-center mb-8">
      <h1 class="text-h4 mb-6">
        The gap between where you are and where you're going
      </h1>

      <VRow justify="center" class="mb-8">
        <VCol cols="12" md="5">
          <VCard variant="outlined" class="pa-6">
            <div class="text-caption text-medium-emphasis mb-2">
              Where You Are
            </div>
            <div v-if="loading" class="text-h5">
              —
            </div>
            <div v-else class="text-h4 font-weight-bold text-primary">
              {{ summary?.net_worth != null ? formatCents(summary.net_worth) : '—' }}
            </div>
            <div class="text-caption text-medium-emphasis mt-1">
              (net worth)
            </div>
          </VCard>
        </VCol>
        <VCol cols="12" md="1" class="d-flex align-center justify-center">
          <VIcon icon="tabler-arrow-right" size="32" />
        </VCol>
        <VCol cols="12" md="5">
          <VCard variant="outlined" class="pa-6">
            <div class="text-caption text-medium-emphasis mb-2">
              Where You Need to Be
            </div>
            <div v-if="loading" class="text-h5">
              —
            </div>
            <div v-else class="text-h4 font-weight-bold text-success">
              {{ summary?.freedom_number != null ? formatCents(summary.freedom_number) : '—' }}
            </div>
            <div class="text-caption text-medium-emphasis mt-1">
              (freedom number)
            </div>
          </VCard>
        </VCol>
      </VRow>

      <VProgressLinear
        :model-value="summary?.freedom_pct_achieved ?? 0"
        color="primary"
        height="12"
        rounded
        class="mx-auto mb-6"
        style="max-width: 400px;"
      />
      <p class="text-body-2 text-medium-emphasis mb-2">
        {{ summary?.freedom_pct_achieved != null ? `${summary.freedom_pct_achieved}% of the way there` : 'Progress will appear after your first snapshot' }}
      </p>

      <p class="text-body-1 text-medium-emphasis mb-8">
        But that's the baseline — without any optimization. Let's find the moves that change everything.
      </p>

      <template v-if="generating">
        <VProgressCircular indeterminate color="primary" size="48" class="mb-3" />
        <p class="text-body-1">
          {{ messages[messageIndex] }}
        </p>
      </template>
      <template v-else-if="showDashboardOption">
        <VAlert type="warning" variant="tonal" class="mb-4 mx-auto" max-width="500">
          Still working on your plan. You can wait or head to your dashboard — we'll notify you when it's ready.
        </VAlert>
        <VBtn color="primary" @click="router.push('/dashboard')">
          Go to Dashboard
        </VBtn>
      </template>
    </div>
    </VContainer>
  </div>
</template>

<style lang="scss" scoped>
/* Match onboarding/register FreedomStack dark theme */
.gap-analysis-page {
  --fs-black: #0a0a0a;
  --fs-black-card: #111111;
  --fs-white: #fafaf9;
  --fs-green: #22c55e;
  --fs-gray-400: #a1a1aa;
  --fs-gray-500: #71717a;
  min-height: 100vh;
  background: var(--fs-black);
  color: var(--fs-white);
}

.gap-analysis-page :deep(.v-container) { color: var(--fs-white); }
.gap-analysis-page :deep(.v-card) {
  background: var(--fs-black-card);
  border: 1px solid rgba(255, 255, 255, 0.06);
  color: var(--fs-white);
}
.gap-analysis-page :deep(.text-medium-emphasis) { color: var(--fs-gray-400); }
</style>
