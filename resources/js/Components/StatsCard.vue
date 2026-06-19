<template>
  <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">{{ label }}</p>
        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ formatted }}</p>
        <p v-if="sub" class="text-xs text-slate-400 mt-1">{{ sub }}</p>
      </div>
      <div :class="`w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 bg-${color}-100 dark:bg-${color}-900/30`">
        <svg :class="`w-6 h-6 text-${color}-600`" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="icon"></svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: String,
  value: [Number, String],
  sub: String,
  color: { type: String, default: 'indigo' },
  icon: String,
})

const formatted = computed(() =>
  typeof props.value === 'number'
    ? props.value.toLocaleString()
    : props.value
)
</script>
