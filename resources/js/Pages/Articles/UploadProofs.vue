<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="backUrl" class="text-gray-400 hover:text-gray-600">←</Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Bukti Tayang Upload</h1>
          <p class="text-gray-500 text-sm">
            Isi tautan bukti tayang untuk setiap platform yang digunakan setelah artikel diunggah.
          </p>
          <p v-if="!canEdit" class="text-amber-600 text-xs mt-1">
            Mode lihat saja. Hanya user uploader yang dapat mengubah bukti tayang.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Judul</p>
              <p class="text-gray-900 font-medium">{{ article.title }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</p>
              <p class="text-gray-900 font-medium">{{ article.status }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Penulis</p>
              <p class="text-gray-900 font-medium">{{ article.author?.name ?? '-' }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kategori</p>
              <p class="text-gray-900 font-medium">{{ article.category?.name ?? '-' }}</p>
            </div>
          </div>

          <form class="space-y-4" @submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="platform in uploadProofPlatforms" :key="platform.key" class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">{{ platform.label }}</label>
                <input
                  v-model="form.upload_proofs[platform.key]"
                  type="url"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :placeholder="platform.placeholder"
                  :disabled="!canEdit"
                />
              </div>
            </div>

            <div class="flex items-center gap-3 pt-2" v-if="canEdit">
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition disabled:opacity-50"
              >
                Simpan Bukti Tayang
              </button>
              <Link :href="backUrl" class="text-gray-500 hover:text-gray-700 text-sm">Kembali</Link>
            </div>
          </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 class="font-semibold text-gray-900 mb-4">Ringkasan</h2>
          <div class="space-y-3 text-sm">
            <div class="flex items-center justify-between">
              <span class="text-gray-500">Terisi</span>
              <span class="font-medium text-gray-900">{{ filledCount }}/{{ uploadProofPlatforms.length }}</span>
            </div>
            <div class="space-y-2">
              <div
                v-for="platform in uploadProofPlatforms"
                :key="`status-${platform.key}`"
                class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2"
              >
                <span class="text-gray-700">{{ platform.label }}</span>
                <span :class="form.upload_proofs[platform.key] ? 'text-emerald-600' : 'text-slate-400'">
                  {{ form.upload_proofs[platform.key] ? 'Ada' : 'Kosong' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const uploadProofPlatforms = [
  { key: 'instagram', label: 'Instagram', placeholder: 'Tempel tautan bukti tayang Instagram' },
  { key: 'facebook', label: 'Facebook', placeholder: 'Tempel tautan bukti tayang Facebook' },
  { key: 'youtube', label: 'YouTube', placeholder: 'Tempel tautan bukti tayang YouTube' },
  { key: 'x', label: 'X', placeholder: 'Tempel tautan bukti tayang X' },
  { key: 'tiktok', label: 'TikTok', placeholder: 'Tempel tautan bukti tayang TikTok' },
  { key: 'website', label: 'Website', placeholder: 'Tempel tautan bukti tayang Website' },
];

function createEmptyUploadProofs() {
  return uploadProofPlatforms.reduce((acc, platform) => {
    acc[platform.key] = '';
    return acc;
  }, {});
}

function normalizeUploadProofs(value) {
  const proofs = createEmptyUploadProofs();

  if (!value) {
    return proofs;
  }

  let source = value;
  if (typeof value === 'string') {
    try {
      source = JSON.parse(value);
    } catch (_) {
      return proofs;
    }
  }

  if (source && typeof source === 'object') {
    uploadProofPlatforms.forEach((platform) => {
      const raw = source[platform.key];
      proofs[platform.key] = typeof raw === 'string' ? raw : raw ? String(raw) : '';
    });
  }

  return proofs;
}

const props = defineProps({
  article: Object,
  updateUrl: String,
  backUrl: String,
  canEdit: { type: Boolean, default: false },
});

const form = useForm({
  upload_proofs: normalizeUploadProofs(props.article.upload_proofs),
});

const filledCount = computed(() => {
  return uploadProofPlatforms.filter((platform) => form.upload_proofs[platform.key]).length;
});

function save() {
  if (!props.canEdit) return;
  form.put(props.updateUrl);
}
</script>
