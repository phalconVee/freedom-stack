<script setup lang="ts">
/**
 * Privacy Policy — public, linked from footer and auth pages.
 */
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})
</script>

<template>
  <div class="legal-page">
    <VContainer class="py-10">
      <RouterLink
        to="/"
        class="legal-back"
      >
        <VIcon icon="tabler-arrow-left" size="20" />
        Back to {{ themeConfig.app.title }}
      </RouterLink>

      <h1 class="legal-title">
        Privacy Policy
      </h1>
      <p class="legal-updated">
        Last updated: February 2026
      </p>

      <div class="legal-content">
        <section class="legal-section">
          <h2>1. Overview</h2>
          <p>
            FreedomStack ("we", "our") respects your privacy. This policy describes how we collect, use, and protect your information when you use our website and services. We provide financial education tools and do not sell your personal data.
          </p>
        </section>

        <section class="legal-section">
          <h2>2. Information we collect</h2>
          <p>
            We collect information you provide directly: name, email, password (hashed), and financial data you enter (expenses, debts, savings, goals) to personalize your Freedom Number and recommendations. We use cookies and similar technologies for authentication and preferences.
          </p>
        </section>

        <section class="legal-section">
          <h2>3. How we use your information</h2>
          <p>
            We use your data to calculate your Freedom Number, generate personalized action plans, and show your progress over time. We do not use your data for advertising. AI-generated recommendations are processed in a way that minimizes exposure of personally identifiable information.
          </p>
        </section>

        <section class="legal-section">
          <h2>4. Data storage and security</h2>
          <p>
            Your data is stored securely. We use industry-standard practices to protect your information. You can export your data from the app at any time (Settings → Data & Privacy).
          </p>
        </section>

        <section class="legal-section">
          <h2>5. Your rights</h2>
          <p>
            You may access, correct, or delete your personal data. You may also request an export of your data. Contact us using the details in the app or on our website to exercise these rights.
          </p>
        </section>

        <section class="legal-section">
          <h2>6. Changes</h2>
          <p>
            We may update this policy from time to time. We will post the updated policy on this page and update the "Last updated" date. Continued use of the service after changes constitutes acceptance.
          </p>
        </section>

        <p class="legal-contact">
          Questions? Contact us through the app or at the support address listed on our website.
        </p>
      </div>
    </VContainer>
  </div>
</template>

<style lang="scss" scoped>
.legal-page {
  --fs-black: #0a0a0a;
  --fs-black-card: #111111;
  --fs-white: #fafaf9;
  --fs-green: #22c55e;
  --fs-gray-400: #a1a1aa;
  --fs-gray-500: #71717a;
  --fs-gray-600: #52525b;
  min-height: 100vh;
  background: var(--fs-black);
  color: var(--fs-white);
  font-family: 'DM Sans', system-ui, sans-serif;
}

.legal-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--fs-gray-400);
  text-decoration: none;
  font-size: 0.9rem;
  margin-bottom: 2rem;
  transition: color 0.2s;
}

.legal-back:hover {
  color: var(--fs-green);
}

.legal-title {
  font-family: 'Instrument Serif', Georgia, serif;
  font-size: 2.25rem;
  letter-spacing: -0.02em;
  margin-bottom: 0.5rem;
}

.legal-updated {
  font-size: 0.85rem;
  color: var(--fs-gray-500);
  margin-bottom: 2.5rem;
}

.legal-content {
  max-width: 680px;
  line-height: 1.7;
}

.legal-section {
  margin-bottom: 2rem;
}

.legal-section h2 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--fs-white);
  margin-bottom: 0.75rem;
}

.legal-section p {
  font-size: 0.95rem;
  color: var(--fs-gray-400);
  margin: 0;
}

.legal-contact {
  margin-top: 2.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  font-size: 0.9rem;
  color: var(--fs-gray-500);
}
</style>
