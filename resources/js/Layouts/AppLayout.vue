<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 bg-white border-r border-slate-100 shadow-sm transition-transform duration-300"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
      <!-- Brand -->
      <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100">
        <img src="/image/logo_mahulu.png" alt="Logo Mahulu" class="w-10 h-10 object-contain flex-shrink-0" />
        <div>
          <span class="text-sm font-bold text-slate-800 leading-none">Pendukung PPID Bappelitbangda Mahulu</span>
          <p class="text-[10px] text-slate-400 mt-0.5">Manajemen Konten</p>
        </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <p class="text-[10px] font-semibold text-slate-400 uppercase px-3 mb-2 tracking-wider">Menu</p>

        <Link href="/dashboard" :class="navLinkClasses($page.url.startsWith('/dashboard'))">
          <span :class="navIconClasses($page.url.startsWith('/dashboard'))">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          </span>
          Dashboard
        </Link>

        <!-- Contributor -->
        <template v-if="user?.role === 'contributor'">
          <p class="text-[10px] font-semibold text-slate-400 uppercase px-3 pt-4 mb-2 tracking-wider">Artikel</p>
          <Link href="/articles" :class="navLinkClasses($page.url === '/articles' || $page.url.startsWith('/articles?') || /^\/articles\/\d+\/edit$/.test($page.url))">
            <span :class="navIconClasses($page.url === '/articles' || $page.url.startsWith('/articles?') || /^\/articles\/\d+\/edit$/.test($page.url))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            Artikel Saya
          </Link>
          <Link href="/news/approved" :class="navLinkClasses($page.url.startsWith('/news/approved'))">
            <span :class="navIconClasses($page.url.startsWith('/news/approved'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            Berita Disetujui
          </Link>
          <Link href="/articles/create" :class="navLinkClasses($page.url.startsWith('/articles/create'))">
            <span :class="navIconClasses($page.url.startsWith('/articles/create'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </span>
            Buat Artikel
          </Link>
        </template>

        <!-- Editor -->
        <template v-if="user?.role === 'editor'">
          <p class="text-[10px] font-semibold text-slate-400 uppercase px-3 pt-4 mb-2 tracking-wider">Editorial</p>
          <Link href="/editor/inbox" :class="navLinkClasses($page.url.startsWith('/editor/inbox') || $page.url.startsWith('/editor/articles'))">
            <span :class="navIconClasses($page.url.startsWith('/editor/inbox') || $page.url.startsWith('/editor/articles'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </span>
            Inbox Review
          </Link>
          <Link href="/editor/contributors" :class="navLinkClasses($page.url.startsWith('/editor/contributors'))">
            <span :class="navIconClasses($page.url.startsWith('/editor/contributors'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            Kelola Kontributor
          </Link>
          <!-- <Link href="/editor/contributors/create" :class="navLinkClasses($page.url.startsWith('/editor/contributors/create'))">
            <span :class="navIconClasses($page.url.startsWith('/editor/contributors/create'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v6m3-3h-6M5 19h6a2 2 0 002-2v-1a4 4 0 10-8 0v1a2 2 0 002 2zm4-13a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </span>
            Tambah Kontributor
          </Link> -->
          <!-- <Link href="/rekap" :class="navLinkClasses($page.url.startsWith('/rekap'))">
            <span :class="navIconClasses($page.url.startsWith('/rekap'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            Rekap Artikel
          </Link> -->
        </template>

        <!-- Leader -->
        <template v-if="user?.role === 'leader'">
          <p class="text-[10px] font-semibold text-slate-400 uppercase px-3 pt-4 mb-2 tracking-wider">Laporan</p>
          <Link href="/news/approved" :class="navLinkClasses($page.url.startsWith('/news/approved'))">
            <span :class="navIconClasses($page.url.startsWith('/news/approved'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            Berita Disetujui
          </Link>
          <Link href="/rekap" :class="navLinkClasses($page.url.startsWith('/rekap'))">
            <span :class="navIconClasses($page.url.startsWith('/rekap'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            Rekap Artikel
          </Link>
        </template>
      </nav>

      <!-- User info -->
      <div class="border-t border-slate-100 px-4 py-4">
          <Link href="/news/approved" :class="navLinkClasses($page.url.startsWith('/news/approved'))">
            <span :class="navIconClasses($page.url.startsWith('/news/approved'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            Berita Disetujui
          </Link>
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-bold flex-shrink-0">
            {{ user?.name?.charAt(0)?.toUpperCase() }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-800 truncate">{{ user?.name }}</p>
            <p class="text-[10px] text-slate-400 capitalize">{{ user?.role }}</p>
          </div>
          <Link href="/logout" method="post" as="button" title="Keluar"
            class="text-slate-400 hover:text-red-500 transition p-1 rounded">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </Link>
        </div>
      </div>
    </aside>

    <!-- Overlay mobile -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/30 md:hidden"></div>

    <!-- Main area -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64">
      <!-- Topbar -->
      <header class="sticky top-0 z-20 bg-white/80 backdrop-blur border-b border-slate-100 h-14 flex items-center px-4 md:px-6 gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-500 hover:text-slate-800 transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <div class="flex-1" />
        <!-- Flash messages inline -->
        <transition name="fade">
          <div v-if="flash" :class="flashClass" class="flex items-center gap-2 text-xs px-3 py-1.5 rounded-full font-medium">
            <span>{{ flash }}</span>
          </div>
        </transition>
      </header>

      <!-- Page content -->
      <main class="flex-1 px-4 md:px-8 py-8">
        <slot />
      </main>

      <footer class="text-center text-[11px] text-slate-300 py-4 border-t border-slate-100">
        Pendukung PPID Bappelitbangda Mahulu &copy; {{ new Date().getFullYear() }}
      </footer>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(false);

const flash = computed(() => page.props.flash?.message || page.props.flash?.error || null);
const flashClass = computed(() => page.props.flash?.error ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700');

function navLinkClasses(active) {
  return [
    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all font-medium w-full',
    active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800',
  ];
}

function navIconClasses(active) {
  return active ? 'text-indigo-600' : 'text-slate-400';
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

