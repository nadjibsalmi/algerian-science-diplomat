<script setup lang="ts">
import { useLocale } from '@/composables'

const { t } = useLocale()
</script>

<template>
  <div class="layout-auth">
    <!-- Brand column (decorative) -->
    <aside class="layout-auth__brand" aria-hidden="true">
      <div class="brand__content">
        <div class="brand__logo">ASD</div>
        <p class="brand__tagline">{{ t('app.tagline') }}</p>
        <div class="brand__decoration" />
      </div>
    </aside>

    <!-- Form column -->
    <main class="layout-auth__form" id="main-content">
      <div class="layout-auth__inner">
        <div class="layout-auth__header">
          <span class="layout-auth__logo" aria-label="ASD">ASD</span>
          <h1 class="layout-auth__title">
            <slot name="title" />
          </h1>
          <p v-if="$slots.subtitle" class="layout-auth__subtitle">
            <slot name="subtitle" />
          </p>
        </div>

        <slot />
      </div>
    </main>
  </div>
</template>

<style scoped>
.layout-auth {
  display: flex;
  min-height: 100dvh;
  background-color: var(--color-white, #fff);
}

/* ── Brand column ───────────────────────────────────────── */
.layout-auth__brand {
  display: none;
  flex: 1;
  background: linear-gradient(
    135deg,
    var(--color-primary-600, #005229) 0%,
    var(--color-primary-800, #003215) 50%,
    #001a09 100%
  );
  position: relative;
  overflow: hidden;
  align-items: center;
  justify-content: center;
  padding: 3rem;
}

@media (min-width: 768px) {
  .layout-auth__brand { display: flex; }
}

.brand__content {
  position: relative;
  z-index: 1;
  text-align: center;
  color: #fff;
}

.brand__logo {
  font-size: 4rem;
  font-weight: 800;
  letter-spacing: -0.04em;
  color: #fff;
  margin-bottom: 1rem;
}

.brand__tagline {
  font-size: 1.125rem;
  color: rgb(255 255 255 / 0.8);
  line-height: 1.6;
  max-width: 22rem;
  margin: 0 auto;
}

.brand__decoration {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 20% 80%, rgb(201 162 39 / 0.12) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgb(210 16 52 / 0.08) 0%, transparent 50%);
  pointer-events: none;
}

/* ── Form column ────────────────────────────────────────── */
.layout-auth__form {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.5rem;
  background-color: var(--color-white, #fff);
}

.layout-auth__inner {
  width: 100%;
  max-width: 26rem;
}

.layout-auth__header { margin-bottom: 2rem; }

.layout-auth__logo {
  display: inline-block;
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--color-primary-500, #006233);
  letter-spacing: -0.04em;
  margin-bottom: 1.25rem;
}

.layout-auth__title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--color-neutral-900, #0f172a);
  margin: 0 0 0.5rem;
  letter-spacing: -0.025em;
}

.layout-auth__subtitle {
  color: var(--color-neutral-500, #64748b);
  font-size: 0.9375rem;
  margin: 0;
}
</style>
