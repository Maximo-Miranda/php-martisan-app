<script setup lang="ts">
import type { SelectRootEmits, SelectRootProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { useVModel } from "@vueuse/core"
import { SelectContent, SelectGroup, SelectIcon, SelectItem, SelectItemIndicator, SelectItemText, SelectPortal, SelectRoot, SelectScrollDownButton, SelectScrollUpButton, SelectTrigger, SelectValue, useForwardPropsEmits } from "reka-ui"
import { Check, ChevronsUpDown } from "lucide-vue-next"
import { cn } from "@/lib/utils"

const props = defineProps<SelectRootProps & { class?: HTMLAttributes["class"] }>()
const emits = defineEmits<SelectRootEmits>()

const forwarded = useForwardPropsEmits(props, emits)

const modelValue = useVModel(props, "modelValue", emits)
</script>

<template>
  <SelectRoot v-bind="forwarded" v-model="modelValue">
    <SelectTrigger
      as-child
      data-slot="select-trigger"
      class="inline-flex w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-colors focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive"
    >
      <button type="button" class="flex w-full items-center justify-between">
        <SelectValue placeholder="Select an option" />
        <SelectIcon>
          <ChevronsUpDown class="size-4 shrink-0 opacity-50" />
        </SelectIcon>
      </button>
    </SelectTrigger>
    <SelectPortal>
      <SelectContent
        data-slot="select-content"
        class="z-50 min-w-[200px] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md"
      >
        <SelectScrollUpButton class="flex items-center justify-center px-2 py-1 text-sm text-muted-foreground">
          ▲
        </SelectScrollUpButton>
        <SelectGroup data-slot="select-group" class="p-1">
          <slot />
        </SelectGroup>
        <SelectScrollDownButton class="flex items-center justify-center px-2 py-1 text-sm text-muted-foreground">
          ▼
        </SelectScrollDownButton>
      </SelectContent>
    </SelectPortal>
  </SelectRoot>
</template>

