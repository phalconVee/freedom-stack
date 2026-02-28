<script setup lang="ts">
definePage({
  meta: {
    layout: 'dashboard-shell',
  },
})

const loading = ref(false)
const generating = ref(false)
const latest = ref<any>(null)
const error = ref('')

async function fetchLatest () {
  loading.value = true
  error.value = ''
  try {
    const res = await $api<{ data: any }>('/recommendations/latest')
    latest.value = res.data
  } catch (_) {
    error.value = 'Failed to load recommendations'
  } finally {
    loading.value = false
  }
}

async function generate () {
  generating.value = true
  error.value = ''
  try {
    const res = await $api<{ id: number; status: string }>('/recommendations/generate', { method: 'POST' })
    if (res.status === 'pending' && res.id) {
      const id = res.id
      const poll = async () => {
        try {
          const statusRes = await $api<{ status: string }>(`/recommendations/${id}/status`)
          if (statusRes.status === 'completed' || statusRes.status === 'failed') {
            await fetchLatest()
            generating.value = false
            return true
          }
        } catch (_) {}
        return false
      }
      const interval = setInterval(async () => {
        const done = await poll()
        if (done) clearInterval(interval)
      }, 2500)
      setTimeout(() => {
        clearInterval(interval)
        generating.value = false
      }, 120000)
    } else {
      generating.value = false
    }
  } catch (e: any) {
    error.value = e?.data?.message || 'Failed to start generation'
    generating.value = false
  }
}

onMounted(fetchLatest)

function format (v: any) {
  if (v == null) return '—'
  if (typeof v === 'string') return v
  return JSON.stringify(v)
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h1 class="text-h3 mb-4">
        AI Recommendations
      </h1>
      <p class="text-body-1 text-medium-emphasis mb-6">
        Get a personalized action plan based on your profile. Complete your financial profile and expenses first.
      </p>
    </VCol>
    <VCol cols="12">
      <VAlert type="info" class="mb-4">
        This is for informational purposes only. Not financial advice.
      </VAlert>
      <VBtn
        color="primary"
        class="mb-4"
        :loading="generating"
        @click="generate"
      >
        Generate recommendations
      </VBtn>
      <VAlert v-if="error" type="error" closable @click:close="error = ''">
        {{ error }}
      </VAlert>
    </VCol>
    <VCol v-if="loading" cols="12">
      <VProgressLinear indeterminate color="primary" />
    </VCol>
    <template v-else-if="latest">
      <VCol cols="12">
        <VCard v-if="latest.key_insight" class="mb-4">
          <VCardTitle>Key insight</VCardTitle>
          <VCardText>
            {{ format(latest.key_insight) }}
          </VCardText>
        </VCard>
        <VCard v-if="latest.recommendations?.length" class="mb-4">
          <VCardTitle>Action plan</VCardTitle>
          <VCardText>
            <VList>
              <VListItem
                v-for="(rec, i) in latest.recommendations"
                :key="i"
                :title="rec.title"
                :subtitle="rec.description"
              />
            </VList>
          </VCardText>
        </VCard>
        <VCard v-if="latest.debt_strategy">
          <VCardTitle>Debt strategy</VCardTitle>
          <VCardText>
            {{ format(latest.debt_strategy) }}
          </VCardText>
        </VCard>
        <p v-if="!latest.key_insight && !latest.recommendations?.length && !latest.debt_strategy" class="text-medium-emphasis">
          No recommendations yet. Click "Generate recommendations" after saving your profile and expenses.
        </p>
      </VCol>
    </template>
    <VCol v-else cols="12">
      <p class="text-medium-emphasis">
        No recommendations yet. Complete your financial profile, then click "Generate recommendations".
      </p>
    </VCol>
  </VRow>
</template>
