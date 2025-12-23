<script setup lang="ts">
import type { SelectTriggerProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { SelectIcon, SelectTrigger } from "reka-ui"
import { ChevronsUpDown } from "lucide-vue-next"
import { cn } from "@/lib/utils"

const props = withDefaults(defineProps<SelectTriggerProps & { class?: HTMLAttributes["class"] }>(), {
  as: "button",
})
</script>

<template>
  <SelectTrigger
    data-slot="select-trigger"
    v-bind="props"
    :class="cn('inline-flex w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-colors focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive', props.class)"
  >
    <slot />
    <SelectIcon>
      <ChevronsUpDown class="size-4 shrink-0 opacity-50" />
    </SelectIcon>
  </SelectTrigger>
</template>
