<script setup lang="ts">
/**
 * Footer — matches freedomstack-landing-page.html
 * Brand + disclaimer left; Privacy, Terms right.
 */
</script>

<template>
  <footer class="front-page-footer">
    <VContainer>
      <div class="footer-inner d-flex flex-wrap justify-space-between align-center gap-4 py-8">
        <div class="footer-left d-flex flex-column gap-2">
          <span class="text-body-1 font-weight-semibold">
            FreedomStack
          </span>
          <p class="text-caption text-medium-emphasis mb-0 footer-disclaimer">
            FreedomStack provides financial information for educational purposes only. This is not financial, investment, or tax advice. Consult a qualified professional before making financial decisions.
          </p>
        </div>
        <div class="footer-links d-flex gap-4">
          <RouterLink
            to="/privacy"
            class="text-caption text-medium-emphasis text-decoration-none"
          >
            Privacy
          </RouterLink>
          <RouterLink
            to="/terms"
            class="text-caption text-medium-emphasis text-decoration-none"
          >
            Terms
          </RouterLink>
        </div>
      </div>
    </VContainer>
  </footer>
</template>

<style lang="scss" scoped>
.front-page-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.footer-disclaimer {
  max-width: 450px;
  line-height: 1.5;
  color: var(--fs-gray-600, #52525b) !important;
}

.footer-links a {
  color: var(--fs-gray-600, #52525b) !important;
}

.footer-links a:hover {
  color: var(--fs-gray-400, #a1a1aa) !important;
}
</style>
