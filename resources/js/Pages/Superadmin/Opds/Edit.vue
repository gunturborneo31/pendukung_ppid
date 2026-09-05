<template>
  <AppLayout>
    <div class="space-y-6 max-w-xl">
      <div>
        <h1 class="text-xl font-bold text-slate-900">Edit OPD</h1>
        <p class="text-slate-400 text-xs mt-0.5">Perbarui data Organisasi Perangkat Daerah</p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Nama OPD</label>
          <input v-model="form.name" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" />
          <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Kode</label>
          <input v-model="form.code" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" />
          <p v-if="form.errors.code" class="text-xs text-red-500 mt-1">{{ form.errors.code }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Alamat</label>
          <input v-model="form.address" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Telepon</label>
          <input v-model="form.phone" type="text" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" />
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
          <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-200" />
          OPD Aktif
        </label>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="form.processing"
            class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm disabled:opacity-50">
            Simpan Perubahan
          </button>
          <Link href="/superadmin/opds" class="border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm hover:bg-slate-50 transition">
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

const props = defineProps({
  opd: Object,
});

const form = useForm({
  name: props.opd.name,
  code: props.opd.code,
  address: props.opd.address,
  phone: props.opd.phone,
  is_active: props.opd.is_active,
});

function submit() {
  form.put(`/superadmin/opds/${props.opd.id}`);
}
</script>
