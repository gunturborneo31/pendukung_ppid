<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Artikel Saya</h1>
        <Link href="/articles/create"
          class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
          + Buat Artikel
        </Link>
      </div>

      <!-- Articles Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div v-if="articles.data?.length">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Judul</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Kategori</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Platform</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="article in articles.data" :key="article.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-900 truncate max-w-xs">{{ article.title }}</div>
                  <div v-if="article.editor_notes && article.status === 'returned'"
                    class="text-xs text-red-500 mt-0.5 truncate max-w-xs">
                    📝 {{ article.editor_notes }}
                  </div>
                  <div class="text-xs text-gray-400 mt-0.5">{{ formatDate(article.created_at) }}</div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                  <span class="text-gray-600 text-xs">{{ article.category?.name ?? '-' }}</span>
                </td>
                <td class="px-4 py-3">
                  <StatusBadge :status="article.status" />
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                  <span class="text-xs text-gray-500">{{ article.target_platform }}</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <Link v-if="['draft', 'returned'].includes(article.status)"
                      :href="`/articles/${article.id}/edit`"
                      class="text-xs text-blue-600 hover:underline">Edit</Link>
                    <button v-if="['draft', 'returned'].includes(article.status)"
                      @click="submitArticle(article)"
                      class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded hover:bg-green-200 transition">
                      Submit
                    </button>
                    <a :href="`/preview/${article.preview_token}`" target="_blank"
                      class="text-xs text-gray-500 hover:text-gray-700">Preview</a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-sm">
            <span class="text-gray-500 text-xs">
              Menampilkan {{ articles.from }}-{{ articles.to }} dari {{ articles.total }} artikel
            </span>
            <div class="flex gap-2">
              <Link v-if="articles.prev_page_url" :href="articles.prev_page_url"
                class="px-3 py-1 border border-gray-300 rounded text-xs hover:bg-gray-50">← Prev</Link>
              <Link v-if="articles.next_page_url" :href="articles.next_page_url"
                class="px-3 py-1 border border-gray-300 rounded text-xs hover:bg-gray-50">Next →</Link>
            </div>
          </div>
        </div>

        <div v-else class="p-12 text-center">
          <div class="text-4xl mb-3">📄</div>
          <p class="text-gray-500 mb-4">Belum ada artikel.</p>
          <Link href="/articles/create"
            class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
            Buat Artikel Pertama
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({ articles: Object });

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function submitArticle(article) {
  if (confirm('Submit artikel untuk review editor?')) {
    router.post(`/articles/${article.id}/submit`);
  }
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
