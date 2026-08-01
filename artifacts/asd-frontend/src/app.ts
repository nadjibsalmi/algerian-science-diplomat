/**
 * app.ts — Vue application factory
 *
 * Creates and configures the Vue application instance with all plugins.
 * Separating app creation from mounting enables:
 *  - SSR-safe usage (createApp per request)
 *  - Unit testing without a real DOM
 *  - Cleaner plugin registration
 */
import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import i18n from './i18n'

// Global styles
import './assets/css/main.css'
import './assets/css/rtl.css'

// Global UI components
import {
  BaseButton,
  BaseInput,
  BaseCard,
  BaseBadge,
  BaseAlert,
  BaseSpinner,
  BaseAvatar,
  BaseDivider,
  BaseModal,
} from './components/ui'

export function createVueApp() {
  const app = createApp(App)
  const pinia = createPinia()

  // ── Plugins ──────────────────────────────────────────────
  app.use(pinia)
  app.use(router)
  app.use(i18n)

  // ── Global components ─────────────────────────────────────
  app.component('BaseButton', BaseButton)
  app.component('BaseInput', BaseInput)
  app.component('BaseCard', BaseCard)
  app.component('BaseBadge', BaseBadge)
  app.component('BaseAlert', BaseAlert)
  app.component('BaseSpinner', BaseSpinner)
  app.component('BaseAvatar', BaseAvatar)
  app.component('BaseDivider', BaseDivider)
  app.component('BaseModal', BaseModal)

  return app
}
