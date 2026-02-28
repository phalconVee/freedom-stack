<script setup lang="ts">
/**
 * My Finances — Expenses. FREEDOMSTACK_USER_JOURNEY.md §6.4
 * URL: /finances/expenses. Manage monthly expense categories.
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
        Expenses
      </h1>
      <p class="text-body-1 text-medium-emphasis mb-6">
        Manage monthly expense categories. Table view with category, amount, essential flag.
      </p>
    </VCol>
    <VCol cols="12">
      <VCard>
        <VCardText>
          <VAlert type="info" variant="tonal">
            Expense management (DataTable, add/edit/delete) will be wired to the expenses API. For now this is a placeholder.
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
