<script setup lang="ts">
/**
 * My Finances — Investments. FREEDOMSTACK_USER_JOURNEY.md §6.6
 * URL: /finances/investments. Manage savings and investment accounts.
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
        Investments
      </h1>
      <p class="text-body-1 text-medium-emphasis mb-6">
        Manage all savings and investment accounts. Contribution priority (401K match, IRA, etc.).
      </p>
    </VCol>
    <VCol cols="12">
      <VCard>
        <VCardText>
          <VAlert type="info" variant="tonal">
            Account list and optimization analysis will be wired to the investments API. Placeholder.
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
