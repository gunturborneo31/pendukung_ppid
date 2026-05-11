<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Artikel Saya</h1>
          <p class="text-slate-400 text-xs mt-0.5">Kelola semua artikel yang kamu buat</p>
        </div>
        <Link href="/articles/create"
          class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Buat Artikel
        </Link>
      </div>

      <!-- Articles Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div v-if="articles.data?.length">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Judul</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden md:table-cell">Penulis</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden md:table-cell">Kategori</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden lg:table-cell">Editor</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="article in articles.data" :key="article.id" class="hover:bg-slate-50 transition">
                <td class="px-5 py-3.5">
                  <div class="font-medium text-slate-800 truncate max-w-xs">{{ article.title }}</div>
                  <div v-if="article.editor_notes && article.status === 'returned'"
                    class="flex items-center gap-1 text-xs text-red-500 mt-0.5 truncate max-w-xs">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ article.editor_notes }}
                  </div>
                  <div class="text-xs text-slate-400 mt-0.5">{{ formatDate(article.created_at) }}</div>
                </td>
                <td class="px-5 py-3.5 hidden md:table-cell">
                  <div class="text-xs">
                    <div class="font-medium text-slate-800">{{ article.author?.name ?? '-' }}</div>
                    <div class="text-slate-500">{{ article.author?.field || 'Umum' }}</div>
                  </div>
                </td>
                <td class="px-5 py-3.5 hidden md:table-cell">
                  <span class="text-slate-500 text-xs bg-slate-100 px-2 py-0.5 rounded-full">{{ article.category?.name ?? 'Umum' }}</span>
                </td>
                <td class="px-5 py-3.5 hidden lg:table-cell">
                  <div v-if="article.editor" class="text-xs">
                    <div class="font-medium text-slate-800">{{ article.editor.name }}</div>
                    <div class="text-slate-500">{{ article.editor.field || 'Umum' }}</div>
                  </div>
                  <span v-else class="text-xs text-slate-400">-</span>
                </td>
                <td class="px-5 py-3.5">
                  <StatusBadge :status="article.status" />
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <Link v-if="['draft', 'returned'].includes(article.status)"
                      :href="`/articles/${article.id}/edit`"
                      class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</Link>
                    <button v-if="['draft', 'returned'].includes(article.status)"
                      @click="deleteArticle(article)"
                      class="text-xs text-red-600 hover:text-red-800 font-medium">
                      Hapus
                    </button>
                    <button v-if="['draft', 'returned'].includes(article.status)"
                      @click="submitArticle(article)"
                      class="text-xs bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg hover:bg-emerald-100 transition font-medium">
                      Submit
                    </button>
                    <a :href="`/preview/${article.preview_token}`" target="_blank"
                      class="text-xs text-slate-400 hover:text-slate-600">Preview</a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="px-5 py-3.5 border-t border-slate-100 flex items-center justify-between">
            <span class="text-slate-400 text-xs">
              {{ articles.from }}–{{ articles.to }} dari {{ articles.total }} artikel
            </span>
            <div class="flex gap-2">
              <Link v-if="articles.prev_page_url" :href="articles.prev_page_url"
                class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs hover:bg-slate-50 text-slate-600">← Prev</Link>
              <Link v-if="articles.next_page_url" :href="articles.next_page_url"
                class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs hover:bg-slate-50 text-slate-600">Next →</Link>
            </div>
          </div>
        </div>

        <div v-else class="py-16 text-center">
          <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <p class="text-slate-500 text-sm mb-4">Belum ada artikel. Yuk mulai menulis!</p>
          <Link href="/articles/create"
            class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
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

function deleteArticle(article) {
  if (confirm('Yakin ingin menghapus artikel ini?')) {
    router.delete(`/articles/${article.id}`);
  }
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
