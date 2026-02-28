<script setup lang="ts">
/**
 * Landing hero — matches freedomstack-landing-page.html
 * Badge, headline, sub, CTA, fine print. Uses Vuexy/Vuetify design system.
 */
</script>

<template>
  <section
    id="home"
    class="landing-hero-section"
  >
    <div class="landing-hero">
      <VContainer>
        <div class="hero-content text-center">
          <VChip
            class="hero-badge mb-6"
            variant="outlined"
            size="small"
            rounded
          >
            <span class="hero-badge-dot" />
            Free · No account required · 60 seconds
          </VChip>

          <h1 class="hero-headline font-weight-normal mb-4">
            Know your number.<br>Build your <em class="text-primary font-italic">plan.</em>
          </h1>

          <p class="hero-sub mx-auto mb-6">
            Most people know what they spend each month.<br>
            Almost nobody knows how much they'd need invested to cover it forever.<br>
            That number changes everything.
          </p>

          <div class="hero-cta-group d-flex flex-column align-center gap-4">
            <VBtn
              :to="{ path: '/calculator' }"
              color="primary"
              class="hero-cta-btn"
            >
              Calculate my Freedom Number
              <VIcon
                icon="tabler-arrow-right"
                class="ms-2 hero-cta-arrow"
                size="22"
              />
            </VBtn>
            <span class="hero-fine text-caption landing-tertiary">
              Takes 60 seconds. No signup. No credit card. Just math.
            </span>
          </div>
        </div>
      </VContainer>
    </div>
  </section>
</template>

<style lang="scss" scoped>
.landing-hero-section {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.landing-hero {
  position: relative;
  padding-block: 6rem 8rem;
}

.landing-hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 800px;
  height: 800px;
  background: radial-gradient(
    circle,
    var(--fs-green-glow, rgba(34, 197, 94, 0.12)) 0%,
    transparent 70%
  );
  pointer-events: none;
  opacity: 0.6;
}

.hero-badge {
  border-color: rgba(255, 255, 255, 0.1);
  font-size: 0.8rem;
  color: var(--fs-gray-400, #a1a1aa);
}

.hero-badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #22c55e;
  margin-inline-end: 0.5rem;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

.hero-headline {
  font-family: "Instrument Serif", Georgia, serif;
  font-size: clamp(2.8rem, 7vw, 5.5rem);
  line-height: 1.05;
  letter-spacing: -0.03em;
  max-width: 800px;
  margin-inline: auto;
  color: var(--fs-white, #fafaf9);
}

.hero-sub {
  font-size: clamp(1.05rem, 2vw, 1.25rem);
  color: var(--fs-gray-400, #a1a1aa);
  max-width: 540px;
  line-height: 1.65;
}

.hero-fine {
  font-size: 0.82rem;
}

/* CTA: solid green background, light text for consistency */
.hero-cta-btn {
  background: var(--fs-green, #22c55e) !important;
  background-color: var(--fs-green, #22c55e) !important;
  color: var(--fs-white, #fafaf9) !important;
  min-height: 52px !important;
  height: auto !important;
  padding: 1rem 2.25rem !important;
  border-radius: 12px !important;
  font-size: 1.05rem !important;
  font-weight: 600 !important;
  text-transform: none !important;
  letter-spacing: normal !important;
  box-shadow: none !important;
  align-items: center !important;
  display: inline-flex !important;

  &:hover {
    background: var(--fs-green, #22c55e) !important;
    background-color: var(--fs-green, #22c55e) !important;
    color: var(--fs-white, #fafaf9) !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(34, 197, 94, 0.3) !important;
  }
}

/* Keep solid green; prevent Vuetify overlay from changing the background */
.hero-cta-btn :deep(.v-btn__overlay) {
  background: transparent !important;
  opacity: 0 !important;
}

.hero-cta-arrow {
  transition: transform 0.2s;
}

.hero-cta-btn:hover .hero-cta-arrow {
  transform: translateX(3px);
}

/* Prevent Vuetify button inner from collapsing height */
.hero-cta-btn :deep(.v-btn__content) {
  height: auto !important;
  min-height: unset !important;
}

@media (max-width: 599px) {
  .hero-headline {
    font-size: clamp(2.2rem, 8vw, 2.8rem) !important;
    line-height: 1.2 !important;
  }

  .landing-hero {
    padding-block: 5rem 5rem;
  }
}
</style>
