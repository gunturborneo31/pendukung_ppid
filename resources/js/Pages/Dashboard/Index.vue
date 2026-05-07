<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
          <p class="text-gray-500 text-sm mt-1">Selamat datang, {{ $page.props.auth.user.name }}</p>
        </div>
        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full uppercase">
          {{ $page.props.auth.user.role }}
        </span>
      </div>

      <!-- Contributor Dashboard -->
      <template v-if="$page.props.auth.user.role === 'contributor'">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Artikel" :value="stats.total" color="blue" icon="📄" />
          <StatCard title="Draft" :value="stats.draft" color="gray" icon="✏️" />
          <StatCard title="Disubmit" :value="stats.submitted" color="yellow" icon="📤" />
          <StatCard title="Dipublikasi" :value="stats.published" color="green" icon="✅" />
        </div>

        <div v-if="stats.returned > 0" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
          ⚠️ <strong>{{ stats.returned }} artikel</strong> dikembalikan oleh editor. Silakan perbaiki dan submit ulang.
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Artikel Terbaru</h2>
          <div v-if="recentArticles?.length" class="space-y-3">
            <div v-for="article in recentArticles" :key="article.id"
              class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ article.title }}</p>
                <p class="text-xs text-gray-500">{{ article.category?.name ?? 'Tanpa Kategori' }}</p>
              </div>
              <StatusBadge :status="article.status" />
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">Belum ada artikel. <Link href="/articles/create" class="text-blue-600 hover:underline">Buat sekarang!</Link></p>
        </div>

        <div class="flex gap-3">
          <Link href="/articles/create"
            class="bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            + Buat Artikel Baru
          </Link>
          <Link href="/articles" class="border border-gray-300 text-gray-700 px-5 py-2.5 rounded-lg text-sm hover:bg-gray-50 transition">
            Lihat Semua Artikel
          </Link>
        </div>
      </template>

      <!-- Editor Dashboard -->
      <template v-if="$page.props.auth.user.role === 'editor'">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <StatCard title="Menunggu Review" :value="stats.inbox" color="yellow" icon="📥" />
          <StatCard title="Disetujui" :value="stats.approved" color="blue" icon="👍" />
          <StatCard title="Total Publikasi" :value="stats.published" color="green" icon="✅" />
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Artikel Menunggu Review</h2>
          <div v-if="inbox?.length" class="space-y-3">
            <div v-for="article in inbox" :key="article.id"
              class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg border border-gray-100 transition">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ article.title }}</p>
                <p class="text-xs text-gray-500">oleh {{ article.author?.name }}</p>
              </div>
              <Link :href="`/editor/articles/${article.id}`"
                class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full hover:bg-blue-200 transition">
                Review →
              </Link>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">Tidak ada artikel menunggu review.</p>
        </div>

        <Link href="/editor/inbox" class="inline-block bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
          Buka Inbox Review
        </Link>
      </template>

      <!-- Leader Dashboard -->
      <template v-if="$page.props.auth.user.role === 'leader'">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Artikel" :value="stats.total" color="blue" icon="📄" />
          <StatCard title="Dipublikasi" :value="stats.published" color="green" icon="✅" />
          <StatCard title="Draft" :value="stats.draft" color="gray" icon="✏️" />
          <StatCard title="Menunggu Review" :value="stats.submitted" color="yellow" icon="📤" />
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Artikel Terbaru Dipublikasi</h2>
          <div v-if="recentPublished?.length" class="space-y-3">
            <div v-for="article in recentPublished" :key="article.id"
              class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ article.title }}</p>
                <p class="text-xs text-gray-500">oleh {{ article.author?.name }} &bull; {{ formatDate(article.published_at) }}</p>
              </div>
              <StatusBadge :status="article.status" />
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">Belum ada artikel dipublikasikan.</p>
        </div>

        <Link href="/rekap" class="inline-block bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
          Lihat Rekap Lengkap
        </Link>
      </template>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
  stats: Object,
  recentArticles: Array,
  inbox: Array,
  recentPublished: Array,
});

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Inline sub-components
const StatCard = {
  props: ['title', 'value', 'color', 'icon'],
  template: `
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
      <div class="text-2xl">{{ icon }}</div>
      <div>
        <div class="text-2xl font-bold text-gray-900">{{ value ?? 0 }}</div>
        <div class="text-xs text-gray-500 mt-0.5">{{ title }}</div>
      </div>
    </div>
  `,
};

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
