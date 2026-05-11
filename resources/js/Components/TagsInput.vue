<template>
  <div>
    <vue3-tags-input
      v-model="tagInput"
      :tags="tags"
      @on-tags-changed="updateTags"
      placeholder="New tag"
      :add-on-key="['Enter', ',']"
      :allow-edit-tags="false"
      :allow-duplicate="false"
      class="mb-1"
    />
    <span class="text-xs text-gray-500">Tekan enter setelah mengisi tag</span>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import Vue3TagsInput from 'vue3-tags-input';

const props = defineProps({
  modelValue: { type: Array, default: () => [] }
});
const emit = defineEmits(['update:modelValue']);

function extractTagText(tag) {
  if (!tag) return '';
  if (typeof tag === 'object') {
    const candidate = tag.text ?? tag.label ?? '';
    return typeof candidate === 'string' ? candidate.trim() : '';
  }

  const value = String(tag).trim();
  if (!value) return '';

  if ((value.startsWith('{') && value.endsWith('}')) || (value.startsWith('[') && value.endsWith(']'))) {
    try {
      const parsed = JSON.parse(value);
      if (parsed && typeof parsed === 'object' && typeof parsed.text === 'string') {
        return parsed.text.trim();
      }
    } catch (_) {
      // Keep raw string when not valid JSON.
    }
  }

  return value;
}

function normalizeTags(values) {
  if (!Array.isArray(values)) return [];
  const unique = new Set();

  return values
    .map((tag) => extractTagText(tag))
    .filter((tag) => {
      if (!tag) return false;
      const key = tag.toLowerCase();
      if (unique.has(key)) return false;
      unique.add(key);
      return true;
    });
}

const tags = ref(normalizeTags(props.modelValue));
const tagInput = ref('');

function updateTags(newTags) {
  const normalized = normalizeTags(newTags);
  tags.value = normalized;
  emit('update:modelValue', normalized);
}

watch(() => props.modelValue, (val) => {
  tags.value = normalizeTags(val);
});
</script>

<style scoped>
.vue3-tags-input__tag {
  background: #f1f5f9;
  color: #2563eb;
  border-radius: 6px;
  padding: 2px 10px;
  margin: 2px;
  font-size: 13px;
  border: none;
}
.vue3-tags-input__input {
  border: none;
  outline: none;
  font-size: 14px;
}
</style>
