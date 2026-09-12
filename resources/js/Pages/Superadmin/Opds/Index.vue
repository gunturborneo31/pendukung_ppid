<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Kelola OPD</h1>
          <p class="text-slate-400 text-xs mt-0.5">Daftar Organisasi Perangkat Daerah yang menggunakan aplikasi ini</p>
        </div>
        <Link href="/superadmin/opds/create"
          class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Tambah OPD
        </Link>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
              <th class="py-2 pr-3">Nama OPD</th>
              <th class="py-2 px-2">Daerah</th>
              <th class="py-2 px-2">Kode</th>
              <th class="py-2 px-2 text-center">Pengguna</th>
              <th class="py-2 px-2 text-center">Artikel</th>
              <th class="py-2 px-2 text-center">Status</th>
              <th class="py-2 pl-2 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="opd in opds.data" :key="opd.id" class="border-b border-slate-50 hover:bg-slate-50">
              <td class="py-3 pr-3 font-medium text-slate-800">{{ opd.name }}</td>
              <td class="py-3 px-2 text-slate-500">{{ opd.daerah ?? '-' }}</td>
              <td class="py-3 px-2 text-slate-500">{{ opd.code }}</td>
              <td class="py-3 px-2 text-center text-slate-500">{{ opd.users_count }}</td>
              <td class="py-3 px-2 text-center text-slate-500">{{ opd.articles_count }}</td>
              <td class="py-3 px-2 text-center">
                <span class="text-xs px-2.5 py-1 rounded-lg font-medium"
                  :class="opd.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'">
                  {{ opd.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="py-3 pl-2 text-right">
                <Link :href="`/superadmin/opds/${opd.id}/edit`" class="text-xs text-indigo-600 hover:underline mr-3">Edit</Link>
                <Link :href="`/superadmin/opds/${opd.id}`" method="delete" as="button"
                  class="text-xs text-red-500 hover:underline"
                  @click.capture="(e) => { if (!confirm('Hapus OPD ini?')) e.preventDefault(); }">
                  Hapus
                </Link>
              </td>
            </tr>
            <tr v-if="!opds.data.length">
              <td colspan="7" class="py-6 text-center text-slate-400">Belum ada OPD terdaftar.</td>
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
  opds: Object,
});
</script>
