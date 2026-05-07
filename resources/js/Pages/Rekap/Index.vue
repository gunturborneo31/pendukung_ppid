<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Rekap Artikel</h1>
          <p class="text-gray-500 text-sm">Laporan dan statistik semua artikel</p>
        </div>
        <div class="flex gap-2">
          <a :href="exportUrl('excel')" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
            📊 Export Excel
          </a>
          <a :href="exportUrl('pdf')" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
            📄 Export PDF
          </a>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <div v-for="(value, key) in stats" :key="key"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
          <div class="text-2xl font-bold text-gray-900">{{ value }}</div>
          <div class="text-xs text-gray-500 mt-1 capitalize">{{ key }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
          <select v-model="localFilters.status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="submitted">Submitted</option>
            <option value="returned">Returned</option>
            <option value="approved">Approved</option>
            <option value="published">Published</option>
          </select>
          <input v-model="localFilters.date_from" type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Dari tanggal" />
          <input v-model="localFilters.date_to" type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Sampai tanggal" />
          <button @click="applyFilters"
            class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            Filter
          </button>
          <button @click="resetFilters"
            class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            Reset
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div v-if="articles.data?.length">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Judul</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Penulis</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Kategori</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Platform</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="article in articles.data" :key="article.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-900 truncate max-w-xs">{{ article.title }}</div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600 text-xs">{{ article.author?.name }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-500 text-xs">{{ article.category?.name ?? '-' }}</td>
                <td class="px-4 py-3">
                  <StatusBadge :status="article.status" />
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-500 text-xs">{{ article.target_platform }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ formatDate(article.created_at) }}</td>
              </tr>
            </tbody>
          </table>

          <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-sm">
            <span class="text-gray-500 text-xs">{{ articles.from }}-{{ articles.to }} dari {{ articles.total }}</span>
            <div class="flex gap-2">
              <Link v-if="articles.prev_page_url" :href="articles.prev_page_url"
                class="px-3 py-1 border border-gray-300 rounded text-xs hover:bg-gray-50">← Prev</Link>
              <Link v-if="articles.next_page_url" :href="articles.next_page_url"
                class="px-3 py-1 border border-gray-300 rounded text-xs hover:bg-gray-50">Next →</Link>
            </div>
          </div>
        </div>
        <div v-else class="p-12 text-center">
          <div class="text-4xl mb-3">📋</div>
          <p class="text-gray-500">Tidak ada data dengan filter ini.</p>
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
        draft: 'bg-gray-100 text-gray-600',
        submitted: 'bg-yellow-100 text-yellow-700',
        returned: 'bg-red-100 text-red-700',
        approved: 'bg-blue-100 text-blue-700',
        published: 'bg-green-100 text-green-700',
      };
      return map[this.status] ?? 'bg-gray-100 text-gray-600';
    },
  },
  template: `<span class="text-xs px-2.5 py-0.5 rounded-full font-medium" :class="classes">{{ status }}</span>`,
};
</script>
