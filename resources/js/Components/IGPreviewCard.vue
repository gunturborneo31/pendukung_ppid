<template>
  <div class="ig-mockup max-w-sm border border-gray-200 rounded-lg overflow-hidden shadow-sm bg-white">
    <!-- Header -->
    <div class="header flex items-center p-3 gap-2 border-b">
      <img src="/image/logo_mahulu.png" alt="Logo Mahulu" class="w-8 h-8 object-contain rounded-full bg-white border border-gray-200 p-1" />
      <div>
        <div class="font-semibold text-sm">ppid_account</div>
        <div class="text-xs text-gray-400">Pendukung PPID Bappelitbangda Mahulu</div>
      </div>
      <div class="ml-auto text-gray-400 text-lg">···</div>
    </div>

    <!-- Carousel Image Preview -->
    <div class="bg-gray-100 aspect-square flex items-center justify-center relative overflow-hidden">
      <template v-if="mediaList.length">
        <img v-if="!isVideoMedia(mediaList[currentIndex])" :src="mediaUrl(currentIndex)" class="w-full h-full object-cover transition-all duration-300" alt="Post image" />
        <video v-else :src="mediaUrl(currentIndex)" class="w-full h-full object-cover transition-all duration-300" controls />
        <!-- Slide Controls -->
        <button v-if="mediaList.length > 1" type="button" @click="prevImage" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-white text-gray-700 rounded-full p-1 shadow">
          ◀
        </button>
        <button v-if="mediaList.length > 1" type="button" @click="nextImage" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-white text-gray-700 rounded-full p-1 shadow">
          ▶
        </button>
        <!-- Media Counter -->
        <div v-if="mediaList.length > 1" class="absolute top-2 left-2 bg-black/50 text-white text-xs px-2 py-1 rounded">
          {{ currentIndex + 1 }}/{{ mediaList.length }}
        </div>
        <!-- Edit/Delete Buttons -->
        <div class="absolute top-2 right-2 flex gap-1">
          <button @click="$emit('edit-media', currentIndex)" class="bg-white/80 hover:bg-blue-100 text-blue-500 rounded-full p-1 shadow" title="Ubah">
            ✏️
          </button>
          <button @click="$emit('delete-media', currentIndex)" class="bg-white/80 hover:bg-red-100 text-red-500 rounded-full p-1 shadow" title="Hapus">
            🗑️
          </button>
        </div>
      </template>
      <div v-else class="text-gray-400 text-center p-4">
        <div class="text-4xl mb-2">🖼</div>
        <div class="text-xs">Belum ada gambar</div>
      </div>
    </div>

    <!-- Thumbnail Indicators -->
    <div v-if="mediaList.length > 1" class="flex gap-1 p-2 bg-gray-50 border-t overflow-x-auto">
      <button v-for="(_, idx) in mediaList" :key="idx" type="button" @click="currentIndex = idx"
        :class="[
          'flex-shrink-0 w-10 h-10 rounded border-2 overflow-hidden transition-all',
          currentIndex === idx ? 'border-blue-500 ring-2 ring-blue-300' : 'border-gray-300 hover:border-gray-400'
        ]">
        <img :src="mediaUrl(idx)" class="w-full h-full object-cover" />
      </button>
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
import { computed, ref, watch } from 'vue';

const props = defineProps({
  caption: { type: String, default: '' },
  hashtags: { type: String, default: '' },
  media: { type: Array, default: () => [] },
});

const emit = defineEmits(['edit-media', 'delete-media']);

const mediaList = computed(() => props.media || []);
const currentIndex = ref(0);

watch(mediaList, (val) => {
  if (val.length === 0) currentIndex.value = 0;
  else if (currentIndex.value >= val.length) currentIndex.value = val.length - 1;
});

function mediaUrl(idx) {
  const m = mediaList.value[idx];
  if (!m) return '';
  if (typeof m === 'string') return m;
  if (m.url) {
    if (m.url.startsWith('http://') || m.url.startsWith('https://') || m.url.startsWith('/')) return m.url;
    return `/${m.url}`;
  }
  if (m.path) {
    if (m.path.startsWith('http://') || m.path.startsWith('https://') || m.path.startsWith('/')) return m.path;
    return `/storage/${m.path}`;
  }
  // Jika File object, buat URL sementara
  if (typeof File !== 'undefined' && m instanceof File) {
    return URL.createObjectURL(m);
  }
  return '';
}

function isVideoMedia(media) {
  if (!media) return false;
  if (typeof media === 'string') return /\.(mp4|mov|webm|ogg)$/i.test(media);
  if (media.type) return media.type.startsWith('video/') || media.type === 'video';
  const source = media.url || media.path || '';
  return /\.(mp4|mov|webm|ogg)$/i.test(source);
}
function prevImage() {
  if (mediaList.value.length > 0) {
    currentIndex.value = (currentIndex.value - 1 + mediaList.value.length) % mediaList.value.length;
  }
}
function nextImage() {
  if (mediaList.value.length > 0) {
    currentIndex.value = (currentIndex.value + 1) % mediaList.value.length;
  }
}

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
