<script setup lang="ts">
/**
 * Terms of Service — public, linked from footer and register page.
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
        Terms of Service
      </h1>
      <p class="legal-updated">
        Last updated: February 2026
      </p>

      <div class="legal-content">
        <section class="legal-section">
          <h2>1. Acceptance</h2>
          <p>
            By using FreedomStack, you agree to these Terms of Service. If you do not agree, do not use our services. We provide tools and content for financial education only.
          </p>
        </section>

        <section class="legal-section">
          <h2>2. Not financial advice</h2>
          <p>
            FreedomStack is for educational and informational purposes only. We do not provide financial, investment, tax, or legal advice. Your Freedom Number, action plans, and recommendations are illustrative and based on the inputs you provide. You should consult a qualified professional before making any financial decisions.
          </p>
        </section>

        <section class="legal-section">
          <h2>3. Your account and data</h2>
          <p>
            You are responsible for keeping your account credentials secure and for the accuracy of the data you enter. You grant us a limited license to use your data to provide and improve our services, in accordance with our <RouterLink to="/privacy" class="legal-inline-link">Privacy Policy</RouterLink>.
          </p>
        </section>

        <section class="legal-section">
          <h2>4. Acceptable use</h2>
          <p>
            You agree to use FreedomStack only for lawful purposes. You may not misuse the service, attempt to gain unauthorized access, or use it in any way that could harm the service or other users.
          </p>
        </section>

        <section class="legal-section">
          <h2>5. Service availability</h2>
          <p>
            We strive to keep the service available but do not guarantee uninterrupted access. We may modify, suspend, or discontinue features with reasonable notice where practical.
          </p>
        </section>

        <section class="legal-section">
          <h2>6. Limitation of liability</h2>
          <p>
            To the fullest extent permitted by law, FreedomStack and its providers are not liable for any indirect, incidental, or consequential damages arising from your use of the service or reliance on its content. Your use of the service is at your own risk.
          </p>
        </section>

        <section class="legal-section">
          <h2>7. Changes to terms</h2>
          <p>
            We may update these terms from time to time. We will post the updated terms on this page and update the "Last updated" date. Continued use of the service after changes constitutes acceptance. Material changes may be communicated via email or in-app notice.
          </p>
        </section>

        <p class="legal-contact">
          Questions about these terms? Contact us through the app or at the support address listed on our website.
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

.legal-inline-link {
  color: var(--fs-green);
  text-decoration: none;
}

.legal-inline-link:hover {
  text-decoration: underline;
}

.legal-contact {
  margin-top: 2.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  font-size: 0.9rem;
  color: var(--fs-gray-500);
}
</style>
