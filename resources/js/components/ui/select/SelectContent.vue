<script setup lang="ts">
import type { SelectContentProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { SelectContent, SelectPortal } from "reka-ui"
import { cn } from "@/lib/utils"

const props = withDefaults(defineProps<SelectContentProps & { class?: HTMLAttributes["class"] }>(), {
  position: "popper",
  sideOffset: 4,
  collisionPadding: 12,
})
</script>

<template>
  <SelectPortal>
    <SelectContent
      data-slot="select-content"
      v-bind="props"
      :class="cn('z-50 min-w-[200px] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md', props.class)"
    >
      <slot />
    </SelectContent>
  </SelectPortal>
</template>
