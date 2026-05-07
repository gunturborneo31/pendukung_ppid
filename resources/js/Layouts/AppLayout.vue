<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Navbar -->
    <nav class="bg-blue-800 text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center gap-6">
            <Link href="/dashboard" class="text-white font-bold text-lg hover:text-blue-200 transition">
              📋 Pendukung PPID
            </Link>
            <div class="hidden md:flex items-center gap-4">
              <Link href="/dashboard" class="text-blue-100 hover:text-white text-sm transition px-2 py-1 rounded" :class="{'text-white font-semibold bg-blue-700': $page.url.startsWith('/dashboard')}">
                Dashboard
              </Link>
              <!-- Contributor links -->
              <template v-if="user?.role === 'contributor'">
                <Link href="/articles" class="text-blue-100 hover:text-white text-sm transition px-2 py-1 rounded" :class="{'text-white font-semibold bg-blue-700': $page.url.startsWith('/articles')}">
                  Artikel Saya
                </Link>
                <Link href="/articles/create" class="text-blue-100 hover:text-white text-sm transition px-2 py-1 rounded">+ Buat Artikel</Link>
              </template>
              <!-- Editor links -->
              <template v-if="user?.role === 'editor'">
                <Link href="/editor/inbox" class="text-blue-100 hover:text-white text-sm transition px-2 py-1 rounded" :class="{'text-white font-semibold bg-blue-700': $page.url.startsWith('/editor')}">
                  Inbox Review
                </Link>
                <Link href="/rekap" class="text-blue-100 hover:text-white text-sm transition px-2 py-1 rounded" :class="{'text-white font-semibold bg-blue-700': $page.url.startsWith('/rekap')}">
                  Rekap
                </Link>
              </template>
              <!-- Leader links -->
              <template v-if="user?.role === 'leader'">
                <Link href="/rekap" class="text-blue-100 hover:text-white text-sm transition px-2 py-1 rounded" :class="{'text-white font-semibold bg-blue-700': $page.url.startsWith('/rekap')}">
                  Rekap
                </Link>
              </template>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-blue-200 text-sm hidden md:block">
              {{ user?.name }} <span class="ml-1 bg-blue-700 px-2 py-0.5 rounded text-xs uppercase">{{ user?.role }}</span>
            </span>
            <Link href="/logout" method="post" as="button"
              class="text-sm bg-blue-700 hover:bg-blue-600 px-3 py-1.5 rounded transition">
              Keluar
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Flash Message -->
    <div v-if="$page.props.flash?.message" class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-3 text-sm">
      ✓ {{ $page.props.flash.message }}
    </div>
    <div v-if="$page.props.flash?.error" class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-3 text-sm">
      ✗ {{ $page.props.flash.error }}
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t text-center text-xs text-gray-400 py-4">
      Pendukung PPID &copy; {{ new Date().getFullYear() }}
    </footer>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

