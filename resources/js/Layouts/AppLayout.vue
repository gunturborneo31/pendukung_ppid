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
            <span v-if="revisionNotesCount > 0" class="ml-auto bg-amber-100 text-amber-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">
              {{ formatBadgeCount(revisionNotesCount) }}
            </span>
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
            <span v-if="pendingVerificationCount > 0" class="ml-auto bg-red-100 text-red-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">
              {{ formatBadgeCount(pendingVerificationCount) }}
            </span>
          </Link>
          <Link href="/editor/contributors" :class="navLinkClasses($page.url.startsWith('/editor/contributors'))">
            <span :class="navIconClasses($page.url.startsWith('/editor/contributors'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            Kelola Kontributor
          </Link>
          <Link href="/editor/approved" :class="navLinkClasses($page.url.startsWith('/editor/approved'))">
            <span :class="navIconClasses($page.url.startsWith('/editor/approved'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            Berita Disetujui Editor
          </Link>
          <Link href="/dashboard/opd" :class="navLinkClasses($page.url.startsWith('/dashboard/opd'))">
            <span :class="navIconClasses($page.url.startsWith('/dashboard/opd'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M9 17V9m4 8V5m4 12v-6"/></svg>
            </span>
            Status Seluruh OPD
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
          <Link href="/dashboard/opd" :class="navLinkClasses($page.url.startsWith('/dashboard/opd'))">
            <span :class="navIconClasses($page.url.startsWith('/dashboard/opd'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M9 17V9m4 8V5m4 12v-6"/></svg>
            </span>
            Status Seluruh OPD
          </Link>
        </template>

        <!-- Superadmin -->
        <template v-if="user?.role === 'superadmin'">
          <p class="text-[10px] font-semibold text-slate-400 uppercase px-3 pt-4 mb-2 tracking-wider">Administrasi</p>
          <Link href="/superadmin/opds" :class="navLinkClasses($page.url.startsWith('/superadmin/opds'))">
            <span :class="navIconClasses($page.url.startsWith('/superadmin/opds'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21H3m4-14h.01M11 7h.01M15 7h.01M7 11h.01M11 11h.01M15 11h.01M7 15h.01M11 15h.01M15 15h.01"/></svg>
            </span>
            Kelola OPD
          </Link>
          <Link href="/superadmin/users" :class="navLinkClasses($page.url.startsWith('/superadmin/users'))">
            <span :class="navIconClasses($page.url.startsWith('/superadmin/users'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            Kelola Editor &amp; Leader
          </Link>
          <Link href="/superadmin/contributors" :class="navLinkClasses($page.url.startsWith('/superadmin/contributors'))">
            <span :class="navIconClasses($page.url.startsWith('/superadmin/contributors'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            Kelola Kontributor
          </Link>

          <p class="text-[10px] font-semibold text-slate-400 uppercase px-3 pt-4 mb-2 tracking-wider">Pengaturan</p>
          <Link href="/superadmin/settings/notifications" :class="navLinkClasses($page.url.startsWith('/superadmin/settings/notifications'))">
            <span :class="navIconClasses($page.url.startsWith('/superadmin/settings/notifications'))">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </span>
            Uji Notifikasi Push
          </Link>
        </template>
      </nav>

      <!-- User info -->
      <div class="border-t border-slate-100 px-4 py-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-bold flex-shrink-0">
            {{ user?.name?.charAt(0)?.toUpperCase() }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-800 truncate">{{ user?.name }}</p>
            <p class="text-[10px] text-slate-400 capitalize">
              {{ user?.role }}<span v-if="user?.opd"> &middot; {{ user.opd.name }}</span>
            </p>
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
const notifications = computed(() => page.props.notifications || {});
const sidebarOpen = ref(false);
const pendingVerificationCount = computed(() => Number(notifications.value.pending_verification || 0));
const revisionNotesCount = computed(() => Number(notifications.value.revision_notes || 0));

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

function formatBadgeCount(value) {
  if (value > 99) {
    return '99+';
  }

  return value;
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
