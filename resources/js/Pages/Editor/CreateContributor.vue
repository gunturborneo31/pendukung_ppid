<template>
  <AppLayout>
    <div class="max-w-xl mx-auto">
      <div class="mb-6">
        <Link :href="route('editor.contributors')" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-slate-600 mb-4">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali
        </Link>
        <h1 class="text-xl font-bold text-slate-900">Tambah Kontributor</h1>
        <p class="text-slate-400 text-xs mt-0.5">Buat akun kontributor baru</p>
      </div>
      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Nama <span class="text-red-400">*</span></label>
          <input v-model="form.name" type="text" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" required />
          <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Email <span class="text-red-400">*</span></label>
          <input v-model="form.email" type="email" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" required />
          <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Bidang</label>
          <input v-model="form.field" type="text" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="Humas, Teknologi, Kesehatan..." />
          <p v-if="form.errors.field" class="text-red-500 text-xs mt-1">{{ form.errors.field }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Password <span class="text-red-400">*</span></label>
          <input v-model="form.password" type="password" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" required minlength="8" />
          <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
        </div>
        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition disabled:opacity-50 shadow-sm">
            Simpan
          </button>
          <Link :href="route('editor.contributors')" class="px-6 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition">Batal</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
  name: '',
  email: '',
  field: '',
  password: '',
});

function submit() {
  form.post(route('editor.contributors.store'));
}
</script>
