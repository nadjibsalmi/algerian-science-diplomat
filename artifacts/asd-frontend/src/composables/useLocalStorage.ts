import { ref, watch } from 'vue'
import type { Ref } from 'vue'

/**
 * Reactive localStorage binding with JSON serialization.
 * Changes to the returned ref are automatically persisted.
 *
 * @param key - localStorage key (namespaced by 'asd:' prefix recommended)
 * @param defaultValue - fallback value when key is missing or unparsable
 */
export function useLocalStorage<T>(key: string, defaultValue: T): Ref<T> {
  function read(): T {
    try {
      const raw = localStorage.getItem(key)
      if (raw === null) return defaultValue
      return JSON.parse(raw) as T
    } catch {
      return defaultValue
    }
  }

  const stored = ref<T>(read()) as Ref<T>

  watch(
    stored,
    (value) => {
      try {
        if (value === null || value === undefined) {
          localStorage.removeItem(key)
        } else {
          localStorage.setItem(key, JSON.stringify(value))
        }
      } catch {
        // Ignore QuotaExceededError and similar
      }
    },
    { deep: true }
  )

  return stored
}
