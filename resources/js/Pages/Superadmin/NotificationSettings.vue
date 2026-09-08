<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-xl font-bold text-slate-900">Pengaturan &middot; Uji Notifikasi Push</h1>
        <p class="text-slate-400 text-xs mt-0.5">
          Pastikan notifikasi antar user berjalan dengan baik: cek status aktivasi notifikasi push tiap akun,
          lalu kirim notifikasi percobaan untuk memverifikasi pengiriman benar-benar sampai ke perangkat user.
        </p>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
              <th class="py-2 pr-3">Nama</th>
              <th class="py-2 px-2">Email</th>
              <th class="py-2 px-2">Role</th>
              <th class="py-2 px-2">OPD</th>
              <th class="py-2 px-2">Status Notifikasi</th>
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
              <td class="py-3 px-2 text-slate-500">{{ user.opd ?? 'Lintas OPD' }}</td>
              <td class="py-3 px-2">
                <span v-if="user.push_enabled" class="text-xs px-2.5 py-1 rounded-lg font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                  Aktif
                </span>
                <span v-else class="text-xs px-2.5 py-1 rounded-lg font-medium bg-slate-50 text-slate-500 border border-slate-100">
                  Belum Aktif
                </span>
              </td>
              <td class="py-3 pl-2 text-right">
                <Link
                  :href="`/superadmin/settings/notifications/${user.id}/test`"
                  method="post"
                  as="button"
                  :disabled="!user.push_enabled || sendingUserId === user.id"
                  class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
                  :class="user.push_enabled
                    ? 'text-indigo-600 border border-indigo-200 hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed'
                    : 'text-slate-300 border border-slate-100 cursor-not-allowed'"
                  :title="user.push_enabled ? 'Kirim notifikasi percobaan ke user ini' : 'User belum mengaktifkan notifikasi push'"
                  @start="sendingUserId = user.id"
                  @finish="sendingUserId = null"
                >
                  Kirim Uji Coba
                </Link>
              </td>
            </tr>
            <tr v-if="!users.length">
              <td colspan="6" class="py-6 text-center text-slate-400">Belum ada akun user.</td>
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
import { ref } from 'vue';

defineProps({
  users: Array,
});

const sendingUserId = ref(null);
</script>
