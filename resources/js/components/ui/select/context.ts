import type { InjectionKey, Ref } from "vue"
import { inject } from "vue"

export type SelectValue = string | number

export interface SelectContext {
  value: Ref<SelectValue | undefined>
  open: Ref<boolean>
  setValue: (value: SelectValue) => void
  toggle: () => void
  close: () => void
  disabled?: boolean
}

export const SELECT_INJECTION_KEY: InjectionKey<SelectContext> = Symbol("select-context")

export function useSelectContext(component: string): SelectContext {
  const context = inject(SELECT_INJECTION_KEY)

  if (!context) {
    throw new Error(`${component} must be used within Select.`)
  }

  return context
}

