<script setup lang="ts">
/**
 * The Problem section — matches freedomstack-landing-page.html
 */
</script>

<template>
  <section
    id="problem"
    class="problem-section reveal"
  >
    <VContainer class="problem-container">
      <div class="section-label text-uppercase">
        // the problem
      </div>
      <h2 class="problem-headline text-h3 text-md-h2 font-weight-normal mb-6">
        You know what you spend.<br>You don't know what it <em class="text-primary font-italic">costs</em> you.
      </h2>
      <p class="problem-text text-body-large text-medium-emphasis mb-4">
        You spend $3,200 a month. Rent, groceries, loans, subscriptions, the occasional dinner out. You know the number. It shows up on your bank statement every 30 days.
      </p>
      <p class="problem-text text-body-large text-medium-emphasis mb-4">
        But here's the question nobody asks: <strong class="text-high-emphasis">how much money would you need — sitting in investments, compounding, working for you — to cover that $3,200 forever?</strong>
      </p>
      <p class="problem-text text-body-large text-medium-emphasis mb-4">
        That's your Freedom Number. And once you know it, every financial decision starts to make sense. Pay off the credit card or invest? Max the 401K or save for a house? Take the raise or negotiate harder?
      </p>
      <p class="problem-text text-body-large text-medium-emphasis mb-0">
        The answer isn't "<span class="strikethrough text-disabled">it depends</span>." It's math. And we do the math for you.
      </p>
    </VContainer>
  </section>
</template>

<style lang="scss" scoped>
.problem-section {
  padding-block: 6rem 6rem;
}

.problem-container {
  max-width: 780px;
}

.section-label {
  font-size: 0.75rem;
  letter-spacing: 0.15em;
  color: var(--fs-green, #22c55e);
  margin-bottom: 2rem;
  font-family: "JetBrains Mono", ui-monospace, monospace;
}

.problem-headline {
  line-height: 1.15;
  letter-spacing: -0.02em;
}

.problem-text {
  line-height: 1.75;
}

.strikethrough {
  text-decoration: line-through;
  color: var(--fs-gray-600, #52525b);
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
