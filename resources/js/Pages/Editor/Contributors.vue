<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Manajemen Kontributor</h1>
          <p class="text-slate-400 text-xs mt-0.5">Kelola akun kontributor yang terdaftar</p>
        </div>
        <Link :href="route('editor.contributors.create')"
          class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Tambah Kontributor
        </Link>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="min-w-full text-sm" v-if="contributors.length">
          <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Nama</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide hidden md:table-cell">Email</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Bidang</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wide">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="user in contributors" :key="user.id" class="hover:bg-slate-50 transition">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                    {{ user.name?.charAt(0)?.toUpperCase() }}
                  </div>
                  <span class="font-medium text-slate-800">{{ user.name }}</span>
                </div>
              </td>
              <td class="px-5 py-3.5 hidden md:table-cell text-slate-500 text-xs">{{ user.email }}</td>
              <td class="px-5 py-3.5">
                <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full">{{ user.field || 'Umum' }}</span>
              </td>
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <Link :href="route('editor.contributors.edit', user.id)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</Link>
                  <button @click="deleteUser(user.id)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="py-16 text-center">
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <p class="text-slate-500 text-sm">Belum ada kontributor terdaftar.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ contributors: Array });

function deleteUser(id) {
  if (confirm('Yakin ingin menghapus akun ini?')) {
    router.delete(route('editor.contributors.destroy', id));
  }
}
</script>
