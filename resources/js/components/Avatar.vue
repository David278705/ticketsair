<template>
  <div :class="containerClass">
    <img 
      v-if="src" 
      :src="src" 
      :alt="alt" 
      :class="imageClass"
      @error="handleError"
    />
    <div v-else :class="placeholderClass">
      <svg :class="iconClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: null
  },
  alt: {
    type: String,
    default: 'Avatar'
  },
  size: {
    type: String,
    default: 'md', // xs, sm, md, lg, xl
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  }
})

const hasError = ref(false)

const sizeClasses = {
  xs: {
    container: 'h-6 w-6',
    icon: 'h-3 w-3'
  },
  sm: {
    container: 'h-8 w-8',
    icon: 'h-4 w-4'
  },
  md: {
    container: 'h-12 w-12',
    icon: 'h-6 w-6'
  },
  lg: {
    container: 'h-16 w-16',
    icon: 'h-8 w-8'
  },
  xl: {
    container: 'h-24 w-24',
    icon: 'h-12 w-12'
  }
}

const containerClass = computed(() => {
  return `${sizeClasses[props.size].container} rounded-full overflow-hidden flex-shrink-0`
})

const imageClass = computed(() => {
  return 'h-full w-full object-cover'
})

const placeholderClass = computed(() => {
  return `h-full w-full bg-indigo-100 flex items-center justify-center`
})

const iconClass = computed(() => {
  return `${sizeClasses[props.size].icon} text-indigo-600`
})

const handleError = () => {
  hasError.value = true
}
</script>
