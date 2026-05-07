<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- SEO Title -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          SEO Title <span class="text-xs" :class="titleLen > 60 ? 'text-red-500' : 'text-gray-400'">{{ titleLen }}/60</span>
        </label>
        <input v-model="local.seo_title" type="text" maxlength="80"
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Judul untuk mesin pencari..." />
      </div>

      <!-- SEO Description -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Meta Description <span class="text-xs" :class="descLen > 160 ? 'text-red-500' : descLen > 120 ? 'text-yellow-500' : 'text-green-500'">{{ descLen }}/160</span>
        </label>
        <textarea v-model="local.seo_description" rows="2" maxlength="160"
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Deskripsi singkat artikel untuk mesin pencari..."></textarea>
      </div>

      <!-- Keywords -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
        <input v-model="local.seo_keywords" type="text"
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
          placeholder="keyword1, keyword2, keyword3" />
      </div>

      <!-- OG Title -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">OG Title</label>
        <input v-model="local.og_title" type="text"
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
          placeholder="Judul untuk media sosial..." />
      </div>

      <!-- OG Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">OG Description</label>
        <input v-model="local.og_description" type="text"
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
          placeholder="Deskripsi untuk media sosial..." />
      </div>

      <!-- OG Image -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">OG Image URL</label>
        <input v-model="local.og_image" type="text"
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
          placeholder="https://..." />
      </div>

      <!-- Canonical URL -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
        <input v-model="local.canonical_url" type="url"
          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
          placeholder="https://..." />
      </div>

      <!-- Robots -->
      <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" v-model="local.robots_index" class="rounded" />
          Index
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" v-model="local.robots_follow" class="rounded" />
          Follow
        </label>
      </div>
    </div>

    <!-- SERP Preview -->
    <div class="border border-gray-200 rounded-lg p-4 bg-white">
      <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Preview Google SERP</h4>
      <div class="max-w-xl">
        <div class="text-blue-700 text-lg hover:underline cursor-pointer leading-tight">
          {{ local.seo_title || 'Judul artikel Anda akan muncul di sini' }}
        </div>
        <div class="text-green-700 text-xs mt-0.5">
          {{ siteUrl }}/artikel/slug-artikel
        </div>
        <div class="text-gray-600 text-sm mt-1 leading-relaxed">
          {{ local.seo_description || 'Deskripsi meta artikel Anda akan muncul di sini. Pastikan deskripsi menarik dan relevan agar pengguna mau mengklik.' }}
        </div>
      </div>
    </div>

    <!-- OG Preview -->
    <div v-if="local.og_title || local.og_description || local.og_image" class="border border-gray-200 rounded-lg overflow-hidden bg-white max-w-sm">
      <h4 class="text-xs font-semibold text-gray-500 uppercase p-3 border-b">Open Graph Preview</h4>
      <img v-if="local.og_image" :src="local.og_image" class="w-full h-48 object-cover" alt="OG Image" @error="e => e.target.style.display='none'" />
      <div class="p-3">
        <div class="text-xs text-gray-500 uppercase">{{ siteUrl }}</div>
        <div class="font-semibold text-sm text-gray-900">{{ local.og_title }}</div>
        <div class="text-xs text-gray-600 mt-1">{{ local.og_description }}</div>
      </div>
    </div>

    <!-- SEO Score -->
    <div class="border border-gray-200 rounded-lg p-4 bg-white">
      <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">SEO Score</h4>
      <div class="flex items-center gap-3 mb-2">
        <div class="flex-1 bg-gray-200 rounded-full h-2">
          <div class="h-2 rounded-full transition-all" :class="scoreColor" :style="{ width: seoScore + '%' }"></div>
        </div>
        <span class="text-sm font-bold" :class="scoreTextColor">{{ seoScore }}/100</span>
      </div>
      <ul class="text-xs space-y-1 text-gray-600">
        <li v-for="check in seoChecks" :key="check.label" :class="check.pass ? 'text-green-600' : 'text-red-500'">
          {{ check.pass ? '✓' : '✗' }} {{ check.label }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, reactive } from 'vue';

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['update:modelValue']);

const siteUrl = window.location.origin;

const local = reactive({
  seo_title: '',
  seo_description: '',
  seo_keywords: '',
  og_title: '',
  og_description: '',
  og_image: '',
  canonical_url: '',
  robots_index: true,
  robots_follow: true,
  ...props.modelValue,
});

watch(() => props.modelValue, (val) => {
  Object.assign(local, val);
}, { deep: true });

watch(local, (val) => {
  emit('update:modelValue', { ...val });
}, { deep: true });

const titleLen = computed(() => local.seo_title?.length ?? 0);
const descLen = computed(() => local.seo_description?.length ?? 0);

const seoChecks = computed(() => [
  { label: 'SEO title diisi (30-60 karakter)', pass: titleLen.value >= 30 && titleLen.value <= 60 },
  { label: 'Meta description diisi (120-160 karakter)', pass: descLen.value >= 120 && descLen.value <= 160 },
  { label: 'Keywords diisi', pass: !!local.seo_keywords },
  { label: 'OG title diisi', pass: !!local.og_title },
  { label: 'OG description diisi', pass: !!local.og_description },
  { label: 'OG image diisi', pass: !!local.og_image },
]);

const seoScore = computed(() => {
  const passing = seoChecks.value.filter(c => c.pass).length;
  return Math.round((passing / seoChecks.value.length) * 100);
});

const scoreColor = computed(() => {
  if (seoScore.value >= 80) return 'bg-green-500';
  if (seoScore.value >= 50) return 'bg-yellow-500';
  return 'bg-red-500';
});

const scoreTextColor = computed(() => {
  if (seoScore.value >= 80) return 'text-green-600';
  if (seoScore.value >= 50) return 'text-yellow-600';
  return 'text-red-600';
});
</script>
