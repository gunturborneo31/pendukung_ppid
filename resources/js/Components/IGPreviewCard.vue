<template>
  <div class="ig-mockup max-w-sm border border-gray-200 rounded-lg overflow-hidden shadow-sm bg-white">
    <!-- Header -->
    <div class="header flex items-center p-3 gap-2 border-b">
      <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">P</div>
      <div>
        <div class="font-semibold text-sm">ppid_account</div>
        <div class="text-xs text-gray-400">Pendukung PPID</div>
      </div>
      <div class="ml-auto text-gray-400 text-lg">···</div>
    </div>

    <!-- Image -->
    <div class="bg-gray-100 aspect-square flex items-center justify-center">
      <img v-if="firstMedia" :src="firstMedia" class="w-full aspect-square object-cover" alt="Post image" />
      <div v-else class="text-gray-400 text-center p-4">
        <div class="text-4xl mb-2">🖼</div>
        <div class="text-xs">Belum ada gambar</div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4 px-3 py-2 text-xl text-gray-700">
      <span>🤍</span>
      <span>💬</span>
      <span>📤</span>
      <span class="ml-auto">🔖</span>
    </div>

    <!-- Caption -->
    <div class="px-3 pb-3 text-sm">
      <div class="font-semibold mb-0.5 text-xs text-gray-500">{{ likesCount }} suka</div>
      <p class="leading-relaxed">
        <span class="font-semibold">ppid_account</span>
        {{ ' ' }}
        <span v-if="caption">{{ displayCaption }}</span>
        <span v-else class="text-gray-400 italic">Caption belum diisi...</span>
        <span v-if="caption && caption.length > 125" class="text-blue-500 cursor-pointer"> ...lebih lanjut</span>
      </p>
      <p v-if="hashtags" class="text-blue-500 text-xs mt-1 leading-relaxed">{{ hashtags }}</p>
      <p class="text-gray-400 text-xs mt-1">{{ timeAgo }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  caption: { type: String, default: '' },
  hashtags: { type: String, default: '' },
  media: { type: Array, default: () => [] },
});

const firstMedia = computed(() => {
  if (!props.media?.length) return null;
  const m = props.media[0];
  return m.url ?? m.path ?? null;
});

const displayCaption = computed(() => {
  if (!props.caption) return '';
  return props.caption.length > 125 ? props.caption.substring(0, 125) : props.caption;
});

const likesCount = computed(() => Math.floor(Math.random() * 500 + 50));

const timeAgo = computed(() => {
  const times = ['2 menit lalu', '5 menit lalu', '1 jam lalu', 'baru saja'];
  return times[Math.floor(Math.random() * times.length)];
});
</script>
