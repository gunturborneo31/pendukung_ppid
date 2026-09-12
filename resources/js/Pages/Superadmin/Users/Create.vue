<template>
  <AppLayout>
    <div class="space-y-6 max-w-xl">
      <div>
        <h1 class="text-xl font-bold text-slate-900">Tambah Akun Editor/Leader/Uploader</h1>
        <p class="text-slate-400 text-xs mt-0.5">OPD bersifat opsional. Jika kosong, akun mendapat akses lintas semua OPD.</p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Nama</label>
          <input v-model="form.name" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" />
          <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" />
          <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Role</label>
          <select v-model="form.role" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
            <option value="editor">Editor</option>
            <option value="leader">Leader</option>
            <option v-if="supportsUploaderRole" value="uploader">Uploader</option>
          </select>
          <p v-if="form.errors.role" class="text-xs text-red-500 mt-1">{{ form.errors.role }}</p>
          <p v-if="!supportsUploaderRole" class="text-xs text-amber-600 mt-1">Role uploader belum aktif di database. Jalankan migrasi terbaru agar opsi ini tersedia.</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Akses OPD (opsional)</label>
          <OpdAccessTablePicker v-model="form.opd_ids" :opds="opds" />
          <p class="text-[11px] text-slate-400 mt-1">Kosongkan semua pilihan jika ingin akses ke semua OPD.</p>
          <p v-if="form.errors.opd_ids" class="text-xs text-red-500 mt-1">{{ form.errors.opd_ids }}</p>
          <p v-if="form.errors['opd_ids.0']" class="text-xs text-red-500 mt-1">{{ form.errors['opd_ids.0'] }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Password</label>
          <input v-model="form.password" type="password" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" />
          <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="form.processing"
            class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm disabled:opacity-50">
            Simpan
          </button>
          <Link href="/superadmin/users" class="border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm hover:bg-slate-50 transition">
            Batal
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import OpdAccessTablePicker from '@/Components/OpdAccessTablePicker.vue';

defineProps({
  opds: Array,
  supportsUploaderRole: { type: Boolean, default: false },
});

const form = useForm({
  name: '',
  email: '',
  role: 'editor',
  opd_ids: [],
  password: '',
});

function submit() {
  form.post('/superadmin/users');
}
</script>
