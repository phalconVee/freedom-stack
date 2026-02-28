<script setup lang="ts">
/**
 * My Finances — Debts. FREEDOMSTACK_USER_JOURNEY.md §6.5
 * URL: /finances/debts. Manage debts and view payoff strategies.
 */
definePage({
  meta: {
    layout: 'dashboard-shell',
  },
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h1 class="text-h3 mb-4">
        Debts
      </h1>
      <p class="text-body-1 text-medium-emphasis mb-6">
        Manage all debts and view avalanche vs snowball payoff comparison.
      </p>
    </VCol>
    <VCol cols="12">
      <VCard>
        <VCardText>
          <VAlert type="info" variant="tonal">
            Debt list and payoff analysis will be wired to the debts API. Placeholder.
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
