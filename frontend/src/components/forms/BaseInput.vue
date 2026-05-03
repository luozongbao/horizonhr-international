<script setup lang="ts">
/**
 * BaseInput — reusable form input wrapper with label and validation message.
 * Full implementation: TASK-021+
 */
defineProps<{
  modelValue?: string | number
  label?: string
  placeholder?: string
  type?: string
  error?: string
  required?: boolean
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div class="form-field">
    <label v-if="label" class="block text-sm font-medium text-brand-text mb-1">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <el-input
      :model-value="modelValue"
      :placeholder="placeholder"
      :type="type ?? 'text'"
      @update:model-value="$emit('update:modelValue', $event)"
    />
    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
  </div>
</template>
