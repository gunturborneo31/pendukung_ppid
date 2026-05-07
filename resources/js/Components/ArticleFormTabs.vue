<template>
  <div>
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="flex gap-1" aria-label="Tabs">
        <button v-for="tab in tabs" :key="tab.id" type="button"
          @click="activeTab = tab.id"
          class="px-4 py-2 text-sm font-medium border-b-2 transition-colors -mb-px"
          :class="activeTab === tab.id
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Tab Panels -->
    <div v-show="activeTab === 'web'">
      <slot name="web" />
    </div>
    <div v-show="activeTab === 'ig'">
      <slot name="ig" />
    </div>
    <div v-show="activeTab === 'seo'">
      <slot name="seo" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  defaultTab: { type: String, default: 'web' },
});

const tabs = [
  { id: 'web', label: '🌐 Konten Web' },
  { id: 'ig', label: '📷 Instagram' },
  { id: 'seo', label: '🔍 SEO' },
];

const activeTab = ref(props.defaultTab);
</script>
