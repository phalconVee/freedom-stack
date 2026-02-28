<script setup lang="ts">
/**
 * Scenarios — Phase 2 teaser. FREEDOMSTACK_USER_JOURNEY.md §6.9
 * URL: /scenarios. "What-if" modeling — coming soon.
 */
definePage({
  meta: {
    layout: 'dashboard-shell',
  },
})

const email = ref('')
const submitted = ref(false)
</script>

<template>
  <VRow justify="center">
    <VCol cols="12" md="8" lg="6">
      <VCard>
        <VCardTitle class="text-h4 d-flex align-center gap-2">
          <VIcon icon="tabler-bolt" size="28" />
          Scenarios — Coming Soon
        </VCardTitle>
        <VCardText>
          <p class="text-body-1 mb-4">
            "What if I get a 20% raise?" "What if I move to a cheaper city?" Model different life events and see how they impact your Freedom Number and timeline in real-time.
          </p>
          <VAlert type="info" variant="tonal" class="mb-4">
            This feature is in the roadmap. Enter your email to be notified when it launches.
          </VAlert>
          <VForm v-if="!submitted" @submit.prevent="submitted = true">
            <AppTextField
              v-model="email"
              type="email"
              label="Email"
              placeholder="you@example.com"
              class="mb-3"
            />
            <VBtn type="submit" color="primary">
              Notify Me When It Launches
            </VBtn>
          </VForm>
          <VAlert v-else type="success">
            Thanks! We'll notify you when Scenarios launches.
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
