<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Rekap Artikel</h1>
          <p class="text-slate-400 text-xs mt-0.5">Laporan dan statistik semua artikel</p>
        </div>
        <div class="flex gap-2">
          <a :href="exportUrl('excel')" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-emerald-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
          </a>
          <a :href="exportUrl('pdf')" class="inline-flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-red-600 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export PDF
          </a>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <div v-for="(value, key) in stats" :key="key"
          class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center hover:shadow-md transition">
          <div class="text-2xl font-bold text-slate-900">{{ value }}</div>
          <div class="text-xs text-slate-400 mt-1 capitalize">{{ key }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
          <select v-model="localFilters.status" class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition text-slate-700">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="submitted">Submitted</option>
            <option value="returned">Returned</option>
            <option value="approved">Approved</option>
            <option value="published">Published</option>
          </select>
          <input v-model="localFilters.date_from" type="date"
            class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500 transition text-slate-700" />
          <input v-model="localFilters.date_to" type="date"
            class="border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500 transition text-slate-700" />
          <button @click="applyFilters"
            class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
            Filter
          </button>
          <button @click="resetFilters"
            class="border border-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm hover:bg-slate-50 transition">
            Reset
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div v-if="articles.data?.length">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Judul</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden md:table-cell">Penulis</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden lg:table-cell">Kategori</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Tanggal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="article in articles.data" :key="article.id" class="hover:bg-slate-50 transition">
                <td class="px-5 py-3.5">
                  <div class="font-medium text-slate-800 truncate max-w-xs">{{ article.title }}</div>
                </td>
                <td class="px-5 py-3.5 hidden md:table-cell">
                  <div class="text-xs">
                    <div class="font-medium text-slate-800">{{ article.author?.name ?? '-' }}</div>
                    <div class="text-slate-500">{{ article.author?.field || 'Umum' }}</div>
                  </div>
                </td>
                <td class="px-5 py-3.5 hidden lg:table-cell">
                  <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ article.category?.name ?? 'Umum' }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <StatusBadge :status="article.status" />
                </td>
                <td class="px-5 py-3.5 text-slate-400 text-xs">{{ formatDate(article.created_at) }}</td>
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
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <p class="text-slate-500 text-sm">Tidak ada data dengan filter ini.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
  articles: Object,
  stats: Object,
  filters: Object,
});

const localFilters = reactive({
  status: props.filters?.status ?? '',
  date_from: props.filters?.date_from ?? '',
  date_to: props.filters?.date_to ?? '',
});

function applyFilters() {
  router.get('/rekap', localFilters, { preserveState: true });
}

function resetFilters() {
  localFilters.status = '';
  localFilters.date_from = '';
  localFilters.date_to = '';
  router.get('/rekap', {}, { preserveState: true });
}

function exportUrl(type) {
  const params = new URLSearchParams(localFilters).toString();
  return `/rekap/export/${type}?${params}`;
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

const StatusBadge = {
  props: ['status'],
  computed: {
    classes() {
      const map = {
        draft: 'bg-slate-100 text-slate-500',
        submitted: 'bg-amber-50 text-amber-700 border border-amber-200',
        returned: 'bg-red-50 text-red-600 border border-red-200',
        approved: 'bg-indigo-50 text-indigo-700 border border-indigo-200',
        published: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
      };
      return map[this.status] ?? 'bg-slate-100 text-slate-500';
    },
  },
  template: `<span class="text-xs px-2.5 py-1 rounded-lg font-medium" :class="classes">{{ status }}</span>`,
};
</script>
