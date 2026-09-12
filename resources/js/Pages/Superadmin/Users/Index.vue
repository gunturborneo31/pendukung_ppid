<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Kelola Editor, Leader &amp; Uploader</h1>
          <p class="text-slate-400 text-xs mt-0.5">Akun editor, leader, dan uploader bisa akses lintas OPD atau dibatasi ke OPD tertentu</p>
        </div>
        <Link href="/superadmin/users/create"
          class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Tambah Akun
        </Link>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
              <th class="py-2 pr-3">Nama</th>
              <th class="py-2 px-2">Email</th>
              <th class="py-2 px-2">Role</th>
              <th class="py-2 px-2">Akses OPD</th>
              <th class="py-2 pl-2 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id" class="border-b border-slate-50 hover:bg-slate-50">
              <td class="py-3 pr-3 font-medium text-slate-800">{{ user.name }}</td>
              <td class="py-3 px-2 text-slate-500">{{ user.email }}</td>
              <td class="py-3 px-2">
                <span class="text-xs px-2.5 py-1 rounded-lg font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 capitalize">{{ user.role }}</span>
              </td>
              <td class="py-3 px-2 text-slate-500">
                <div v-if="user.accessible_opds?.length" class="flex flex-wrap gap-1">
                  <span v-for="opd in user.accessible_opds" :key="`${user.id}-${opd.id}`" class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-xs">
                    {{ opd.name }}
                  </span>
                </div>
                <span v-else>Lintas OPD</span>
              </td>
              <td class="py-3 pl-2 text-right">
                <Link :href="`/superadmin/users/${user.id}/edit`" class="text-xs text-indigo-600 hover:underline mr-3">Edit</Link>
                <Link :href="`/superadmin/users/${user.id}`" method="delete" as="button"
                  class="text-xs text-red-500 hover:underline"
                  @click.capture="(e) => { if (!confirm('Hapus akun ini?')) e.preventDefault(); }">
                  Hapus
                </Link>
              </td>
            </tr>
            <tr v-if="!users.length">
              <td colspan="5" class="py-6 text-center text-slate-400">Belum ada akun editor/leader/uploader.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  users: Array,
});
</script>
