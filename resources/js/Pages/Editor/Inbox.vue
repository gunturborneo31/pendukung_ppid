<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Inbox Review</h1>
          <p class="text-gray-500 text-sm">Artikel menunggu review editor</p>
        </div>
        <span class="bg-yellow-100 text-yellow-800 text-sm font-medium px-3 py-1 rounded-full">
          {{ articles.total }} artikel
        </span>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div v-if="articles.data?.length">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Artikel</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Penulis</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Kategori</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="article in articles.data" :key="article.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-900 truncate max-w-xs">{{ article.title }}</div>
                  <div class="text-xs text-gray-400 mt-0.5">{{ formatDate(article.created_at) }}</div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                  <span class="text-sm text-gray-700">{{ article.author?.name }}</span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                  <span class="text-xs text-gray-500">{{ article.category?.name ?? '-' }}</span>
                </td>
                <td class="px-4 py-3">
                  <StatusBadge :status="article.status" />
                </td>
                <td class="px-4 py-3">
                  <Link :href="`/editor/articles/${article.id}`"
                    class="text-sm text-blue-600 hover:underline font-medium">Review →</Link>
                </td>
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
          <div class="text-4xl mb-3">✅</div>
          <p class="text-gray-500">Tidak ada artikel menunggu review.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ articles: Object });

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

const StatusBadge = {
  props: ['status'],
  computed: {
    classes() {
      const map = {
        submitted: 'bg-yellow-100 text-yellow-700',
        returned: 'bg-red-100 text-red-700',
      };
      return map[this.status] ?? 'bg-gray-100 text-gray-600';
    },
  },
  template: `<span class="text-xs px-2.5 py-0.5 rounded-full font-medium" :class="classes">{{ status }}</span>`,
};
</script>
