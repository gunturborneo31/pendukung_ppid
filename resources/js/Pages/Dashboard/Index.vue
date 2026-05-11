<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Dashboard</h1>
          <p class="text-slate-400 text-xs mt-0.5">Selamat datang, {{ $page.props.auth.user.name }}</p>
        </div>
        <span class="bg-indigo-50 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full uppercase tracking-wide border border-indigo-100">
          {{ $page.props.auth.user.role }}
        </span>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-sm font-semibold text-slate-700">Rekapan Semua Artikel</h2>
          <Link href="/rekap" class="text-xs text-indigo-600 hover:underline">Lihat detail</Link>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
          <div v-for="item in overallStatCards" :key="item.key"
            class="bg-slate-50 rounded-xl border border-slate-100 p-3 text-center">
            <div class="text-lg font-bold text-slate-900">{{ item.value ?? 0 }}</div>
            <div class="text-[11px] text-slate-500 mt-1">{{ item.label }}</div>
          </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[1.3fr_.9fr] gap-4 mt-5">
          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="flex items-center justify-between mb-3">
              <div>
                <h3 class="text-sm font-semibold text-slate-800">Grafik Artikel 6 Bulan</h3>
                <p class="text-xs text-slate-500 mt-1">Jumlah artikel yang dibuat per bulan</p>
              </div>
              <span class="text-xs text-slate-400">Total {{ overallStats?.total ?? 0 }}</span>
            </div>

            <div class="flex items-end gap-3 h-56">
              <div v-for="item in monthlyTrendChart" :key="item.label" class="flex-1 min-w-0 flex flex-col items-center justify-end gap-2 h-full">
                <div class="w-full flex items-end justify-center h-44">
                  <div
                    class="w-full max-w-14 rounded-t-2xl bg-gradient-to-t from-indigo-600 via-indigo-500 to-sky-400 transition-all duration-300"
                    :style="{ height: `${item.height}px`, minHeight: item.value > 0 ? '18px' : '8px' }"
                  />
                </div>
                <div class="text-center">
                  <div class="text-sm font-semibold text-slate-800">{{ item.value }}</div>
                  <div class="text-[11px] text-slate-500 uppercase tracking-wide">{{ item.label }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="mb-3">
              <h3 class="text-sm font-semibold text-slate-800">Chart Status Artikel</h3>
              <p class="text-xs text-slate-500 mt-1">Distribusi artikel berdasarkan status saat ini</p>
            </div>

            <div class="space-y-3">
              <div v-for="item in statusChartBars" :key="item.key" class="space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                  <span class="font-medium text-slate-700">{{ item.label }}</span>
                  <span class="text-slate-500">{{ item.value }} • {{ item.percent }}%</span>
                </div>
                <div class="h-2.5 rounded-full bg-slate-200 overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-300" :class="item.barClass" :style="{ width: `${item.percent}%` }" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contributor Dashboard -->
      <template v-if="$page.props.auth.user.role === 'contributor'">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Total Artikel" :value="stats.total" color="blue" icon="📄" />
          <StatCard title="Draft" :value="stats.draft" color="gray" icon="✏️" />
          <StatCard title="Disubmit" :value="stats.submitted" color="yellow" icon="📤" />
          <StatCard title="Dipublikasi" :value="stats.published" color="green" icon="✅" />
        </div>

        <div v-if="stats.returned > 0" class="flex items-center gap-3 bg-red-50 border border-red-100 rounded-2xl px-5 py-3.5 text-sm text-red-700">
          <svg class="w-5 h-5 flex-shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span><strong>{{ stats.returned }} artikel</strong> dikembalikan editor. Silakan perbaiki dan submit ulang.</span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
          <h2 class="text-sm font-semibold text-slate-700 mb-4">Artikel Terbaru</h2>
          <div v-if="recentArticles?.length" class="space-y-2">
            <div v-for="article in recentArticles" :key="article.id"
              class="flex items-center justify-between px-3 py-2.5 hover:bg-slate-50 rounded-xl transition">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ article.title }}</p>
                <p class="text-xs text-slate-400">{{ article.category?.name ?? 'Tanpa Kategori' }}</p>
              </div>
              <StatusBadge :status="article.status" />
            </div>
          </div>
          <p v-else class="text-sm text-slate-400">Belum ada artikel. <Link href="/articles/create" class="text-indigo-600 hover:underline">Buat sekarang!</Link></p>
        </div>

        <div class="flex gap-3">
          <Link href="/articles/create"
            class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Buat Artikel Baru
          </Link>
          <Link href="/articles" class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm hover:bg-slate-50 transition">
            Lihat Semua
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

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
          <h2 class="text-sm font-semibold text-slate-700 mb-4">Artikel Menunggu Review</h2>
          <div v-if="inbox?.length" class="space-y-2">
            <div v-for="article in inbox" :key="article.id"
              class="flex items-center justify-between px-3 py-2.5 hover:bg-slate-50 rounded-xl border border-slate-100 transition">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ article.title }}</p>
                <p class="text-xs text-slate-400">oleh {{ article.author?.name }}</p>
              </div>
              <Link :href="`/editor/articles/${article.id}`"
                class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition font-medium">
                Review →
              </Link>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400">Tidak ada artikel menunggu review.</p>
        </div>

        <Link href="/editor/inbox" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
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

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
          <h2 class="text-sm font-semibold text-slate-700 mb-4">Artikel Terbaru Dipublikasi</h2>
          <div v-if="recentPublished?.length" class="space-y-2">
            <div v-for="article in recentPublished" :key="article.id"
              class="flex items-center justify-between px-3 py-2.5 hover:bg-slate-50 rounded-xl transition">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ article.title }}</p>
                <p class="text-xs text-slate-400">oleh {{ article.author?.name }} &bull; {{ formatDate(article.published_at) }}</p>
              </div>
              <StatusBadge :status="article.status" />
            </div>
          </div>
          <p v-else class="text-sm text-slate-400">Belum ada artikel dipublikasikan.</p>
        </div>

        <Link href="/rekap" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
          Lihat Rekap Lengkap
        </Link>
      </template>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  stats: Object,
  overallStats: Object,
  statusChart: Array,
  monthlyTrend: Array,
  recentArticles: Array,
  inbox: Array,
  recentPublished: Array,
});

const overallLabels = {
  total: 'Total',
  draft: 'Draft',
  submitted: 'Submitted',
  returned: 'Returned',
  approved: 'Approved',
  published: 'Published',
};

const overallStatCards = computed(() =>
  Object.entries(props.overallStats ?? {}).map(([key, value]) => ({
    key,
    label: overallLabels[key] ?? key,
    value,
  }))
);

const monthlyTrendChart = computed(() => {
  const items = props.monthlyTrend ?? [];
  const maxValue = Math.max(...items.map((item) => item.total || 0), 1);

  return items.map((item) => ({
    label: item.label,
    value: item.total || 0,
    height: Math.max(Math.round(((item.total || 0) / maxValue) * 176), 8),
  }));
});

const statusBarClassMap = {
  draft: 'bg-slate-400',
  submitted: 'bg-amber-400',
  returned: 'bg-rose-400',
  approved: 'bg-indigo-500',
  published: 'bg-emerald-500',
};

const statusChartBars = computed(() => {
  const total = props.overallStats?.total || 0;

  return (props.statusChart ?? []).map((item) => {
    const percent = total > 0 ? Math.round((item.value / total) * 100) : 0;

    return {
      ...item,
      percent,
      barClass: statusBarClassMap[item.key] ?? 'bg-slate-400',
    };
  });
});

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Inline sub-components
const StatCard = {
  props: ['title', 'value', 'color', 'icon'],
  template: `
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4 hover:shadow-md transition">
      <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-lg">{{ icon }}</div>
      <div>
        <div class="text-2xl font-bold text-slate-900">{{ value ?? 0 }}</div>
        <div class="text-xs text-slate-400 mt-0.5">{{ title }}</div>
      </div>
    </div>
  `,
};

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
