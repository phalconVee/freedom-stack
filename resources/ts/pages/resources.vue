<script setup lang="ts">
/**
 * Resources — Phase 2 teaser. FREEDOMSTACK_USER_JOURNEY.md §6.10
 * URL: /resources. Financial education content — coming soon.
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
          <VIcon icon="tabler-book" size="28" />
          Financial Freedom Resources — Coming Soon
        </VCardTitle>
        <VCardText>
          <p class="text-body-1 mb-4">
            Curated guides and explainers built for your situation: The 4% Rule, Federal Student Loan Playbook, 401K vs Roth IRA, HYSA vs I-Bonds, Debt Avalanche vs Snowball, and more.
          </p>
          <VAlert type="info" variant="tonal" class="mb-4">
            Enter your email to be notified when Resources launches.
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
            Thanks! We'll notify you when Resources launches.
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
