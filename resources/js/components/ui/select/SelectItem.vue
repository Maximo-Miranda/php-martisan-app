<script setup lang="ts">
import type { SelectItemProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { SelectItem, SelectItemIndicator, SelectItemText } from "reka-ui"
import { Check } from "lucide-vue-next"
import { cn } from "@/lib/utils"

const props = defineProps<SelectItemProps & { class?: HTMLAttributes["class"] }>()
</script>

<template>
  <SelectItem
    data-slot="select-item"
    v-bind="props"
    class="outline-none"
  >
    <div
      :class="cn('flex cursor-default select-none items-center gap-2 rounded-sm px-3 py-2 text-sm transition-colors data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[state=checked]:font-medium', props.class)"
    >
      <SelectItemIndicator data-slot="select-item-indicator" class="flex items-center text-primary">
        <Check class="size-4" />
      </SelectItemIndicator>
      <SelectItemText data-slot="select-item-text">
        <slot />
      </SelectItemText>
    </div>
  </SelectItem>
</template>
