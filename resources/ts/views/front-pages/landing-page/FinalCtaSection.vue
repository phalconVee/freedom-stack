<script setup lang="ts">
/**
 * Final CTA — matches freedomstack-landing-page.html
 */
</script>

<template>
  <section class="final-cta-section reveal">
    <div class="final-cta-inner">
      <h2 class="text-h3 text-md-h2 text-center font-weight-normal mb-4">
        Your freedom has<br>a <em class="text-primary font-italic">number.</em>
      </h2>
      <p class="text-body-large text-medium-emphasis text-center mx-auto mb-8">
        It takes 60 seconds to find out what it is.<br>
        No signup. No credit card. Just the math that changes how you think about money.
      </p>
      <div class="d-flex justify-center">
        <VBtn
          :to="{ path: '/calculator' }"
          color="primary"
          size="x-large"
          class="px-10"
        >
          Calculate my Freedom Number
          <VIcon
            icon="tabler-arrow-right"
            class="ms-2"
            size="22"
          />
        </VBtn>
      </div>
    </div>
  </section>
</template>

<style lang="scss" scoped>
.final-cta-section {
  padding-block: 8rem;
  position: relative;
}

.final-cta-section::before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 900px;
  height: 600px;
  background: radial-gradient(
    circle,
    var(--fs-green-glow, rgba(34, 197, 94, 0.12)) 0%,
    transparent 70%
  );
  pointer-events: none;
  opacity: 0.4;
}

.final-cta-inner {
  position: relative;
  z-index: 1;
}

.reveal {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}

.reveal.visible {
  opacity: 1;
  transform: translateY(0);
}
</style>
