<script setup lang="ts">
import { ref } from 'vue'
import { useLocale } from '@/composables'
import { useRTL } from '@/composables'

const { t } = useLocale()
const { isRTL } = useRTL()
const sidebarOpen = ref(false)
</script>

<template>
  <div :class="['layout-default', { 'layout-default--rtl': isRTL }]">
    <!-- Top navigation bar -->
    <header class="layout-default__navbar">
      <div class="navbar__inner">
        <div class="navbar__brand">
          <button
            class="navbar__menu-toggle"
            type="button"
            :aria-label="t('nav.home')"
            :aria-expanded="sidebarOpen"
            @click="sidebarOpen = !sidebarOpen"
          >
            <span class="navbar__menu-icon" />
          </button>
          <span class="navbar__logo">ASD</span>
        </div>

        <nav class="navbar__links" :aria-label="t('nav.home')">
          <slot name="nav" />
        </nav>

        <div class="navbar__actions">
          <slot name="actions" />
        </div>
      </div>
    </header>

    <div class="layout-default__body">
      <!-- Sidebar -->
      <aside
        :class="['layout-default__sidebar', { 'layout-default__sidebar--open': sidebarOpen }]"
        :aria-label="'Sidebar navigation'"
      >
        <nav class="sidebar__nav">
          <slot name="sidebar" />
        </nav>
      </aside>

      <!-- Overlay for mobile sidebar -->
      <div
        v-if="sidebarOpen"
        class="layout-default__overlay"
        aria-hidden="true"
        @click="sidebarOpen = false"
      />

      <!-- Main content -->
      <main class="layout-default__main" id="main-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.layout-default {
  display: flex;
  flex-direction: column;
  min-height: 100dvh;
  background-color: var(--color-neutral-50, #f8fafc);
  color: var(--color-neutral-900, #0f172a);
}

/* ── Navbar ─────────────────────────────────────────────── */
.layout-default__navbar {
  position: sticky;
  top: 0;
  z-index: var(--z-sticky, 1100);
  background-color: var(--color-white, #fff);
  border-bottom: 1px solid var(--color-neutral-200, #e2e8f0);
  height: 3.5rem;
}

.navbar__inner {
  display: flex;
  align-items: center;
  gap: 1rem;
  height: 100%;
  padding: 0 1.25rem;
  max-width: 100%;
}

.navbar__brand { display: flex; align-items: center; gap: 0.625rem; flex-shrink: 0; }

.navbar__logo {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-primary-500, #006233);
  letter-spacing: -0.025em;
}

.navbar__menu-toggle {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.375rem;
  border-radius: var(--radius-DEFAULT, 0.375rem);
  color: var(--color-neutral-600, #475569);
}

.navbar__menu-icon,
.navbar__menu-icon::before,
.navbar__menu-icon::after {
  display: block;
  width: 1.25rem;
  height: 2px;
  background-color: currentColor;
  border-radius: 2px;
  transition: transform 200ms ease;
}
.navbar__menu-icon {
  position: relative;
}
.navbar__menu-icon::before,
.navbar__menu-icon::after {
  content: '';
  position: absolute;
  inset-inline-start: 0;
}
.navbar__menu-icon::before { top: -6px; }
.navbar__menu-icon::after  { top: 6px; }

.navbar__links { display: flex; align-items: center; gap: 0.25rem; flex: 1; }
.navbar__actions { display: flex; align-items: center; gap: 0.5rem; margin-inline-start: auto; }

/* ── Body ───────────────────────────────────────────────── */
.layout-default__body {
  display: flex;
  flex: 1;
  min-height: 0;
  position: relative;
}

/* ── Sidebar ────────────────────────────────────────────── */
.layout-default__sidebar {
  width: 15rem;
  flex-shrink: 0;
  background-color: var(--color-white, #fff);
  border-inline-end: 1px solid var(--color-neutral-200, #e2e8f0);
  padding: 1rem 0;
  overflow-y: auto;
}

.sidebar__nav { display: flex; flex-direction: column; gap: 0.25rem; padding: 0 0.75rem; }

.layout-default__overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgb(0 0 0 / 0.4);
  z-index: var(--z-overlay, 1200);
}

/* ── Main ───────────────────────────────────────────────── */
.layout-default__main {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  min-width: 0;
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 767px) {
  .navbar__menu-toggle { display: flex; align-items: center; }

  .layout-default__sidebar {
    position: fixed;
    inset-block: 3.5rem 0;
    inset-inline-start: -15rem;
    z-index: var(--z-modal, 1300);
    transition: inset-inline-start 200ms ease;
    box-shadow: 4px 0 20px rgb(0 0 0 / 0.08);
  }

  .layout-default__sidebar--open {
    inset-inline-start: 0;
  }

  .layout-default__sidebar--open ~ .layout-default__overlay {
    display: block;
  }

  .layout-default__main { padding: 1rem; }
}

/* ── RTL ────────────────────────────────────────────────── */
.layout-default--rtl .layout-default__sidebar {
  border-inline-end: none;
  border-inline-start: 1px solid var(--color-neutral-200, #e2e8f0);
}
</style>
