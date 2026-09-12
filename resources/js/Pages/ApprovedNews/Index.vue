<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Berita Disetujui</h1>
          <p class="text-slate-400 text-xs mt-0.5">Semua berita yang sudah disetujui dan dipublikasikan</p>
        </div>
        <span class="bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-emerald-200">
          {{ articles.total }} berita
        </span>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div v-if="articles.data?.length">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Judul</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden md:table-cell">Penulis</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden lg:table-cell">Editor</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden lg:table-cell">Kategori</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="article in articles.data" :key="article.id" class="hover:bg-slate-50 transition">
                <td class="px-5 py-3.5">
                  <div class="font-medium text-slate-800 truncate max-w-xs">{{ article.title }}</div>
                  <div class="mt-1">
                    <span
                      class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium"
                      :class="hasUploadProofs(article.upload_proofs) ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500'">
                      {{ hasUploadProofs(article.upload_proofs) ? 'Uploading diisi' : 'Uploading kosong' }}
                    </span>
                  </div>
                  <div class="text-xs text-slate-400 mt-0.5">{{ formatDate(article.published_at || article.created_at) }}</div>
                </td>
                <td class="px-5 py-3.5 hidden md:table-cell">
                  <div class="text-xs">
                    <div class="font-medium text-slate-800">{{ article.author?.name ?? '-' }}</div>
                    <div class="text-slate-500">{{ article.author?.field || 'Umum' }}</div>
                  </div>
                </td>
                <td class="px-5 py-3.5 hidden lg:table-cell">
                  <div class="text-xs">
                    <div class="font-medium text-slate-800">{{ article.editor?.name ?? '-' }}</div>
                    <div class="text-slate-500">{{ article.editor?.field || 'Umum' }}</div>
                  </div>
                </td>
                <td class="px-5 py-3.5 hidden lg:table-cell">
                  <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ article.category?.name ?? 'Umum' }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <StatusBadge :status="article.status" />
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <Link :href="detailUrl(article.id)" class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition font-medium">
                      {{ detailLabel }}
                    </Link>
                    <Link v-if="detailLabel === 'Detail'" :href="`/upload-proofs/articles/${article.id}`" class="text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition font-medium">
                      Bukti Tayang
                    </Link>
                    <a :href="`/preview/${article.preview_token}`" target="_blank" class="text-xs text-slate-500 hover:text-slate-700 font-medium">
                      Preview
                    </a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="px-5 py-3.5 border-t border-slate-100 flex items-center justify-between">
            <span class="text-slate-400 text-xs">{{ articles.from }}–{{ articles.to }} dari {{ articles.total }}</span>
            <div class="flex gap-2">
              <Link v-if="articles.prev_page_url" :href="articles.prev_page_url"
                class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs hover:bg-slate-50 text-slate-600">← Prev</Link>
              <Link v-if="articles.next_page_url" :href="articles.next_page_url"
                class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs hover:bg-slate-50 text-slate-600">Next →</Link>
            </div>
          </div>
        </div>

        <div v-else class="py-16 text-center">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="text-slate-500 text-sm">Belum ada berita yang disetujui.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({ articles: Object });
const page = usePage();
const role = computed(() => page.props.auth?.user?.role ?? '');
const detailLabel = computed(() => role.value === 'editor' ? 'Detail' : 'Bukti Tayang');

function detailUrl(articleId) {
  return role.value === 'editor'
    ? `/editor/articles/${articleId}`
    : `/upload-proofs/articles/${articleId}`;
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function hasUploadProofs(value) {
  if (!value) return false;
  const source = typeof value === 'string' ? (() => {
    try {
      return JSON.parse(value);
    } catch (_) {
      return null;
    }
  })() : value;

  if (!source || typeof source !== 'object') return false;

  return Object.values(source).some((item) => typeof item === 'string' && item.trim());
}

const StatusBadge = {
  props: ['status'],
  computed: {
    classes() {
      const map = {
        approved: 'bg-indigo-50 text-indigo-700 border border-indigo-200',
        published: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
      };
      return map[this.status] ?? 'bg-slate-100 text-slate-500';
    },
  },
  template: `<span class="text-xs px-2.5 py-1 rounded-lg font-medium" :class="classes">{{ status }}</span>`,
};
</script>