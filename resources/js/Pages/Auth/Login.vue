<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Left branding panel -->
    <div class="hidden lg:flex flex-col justify-between w-1/2 bg-indigo-700 p-12">
      <div class="flex items-center gap-3">
        <img src="/image/logo_mahulu.png" alt="Logo Mahulu" class="w-11 h-11 object-contain" />
        <span class="text-white font-bold text-base">Pendukung PPID Bappelitbangda Mahulu</span>
      </div>
      <div>
        <h2 class="text-3xl font-bold text-white leading-snug mb-3">Kelola konten PPID<br/>dengan mudah & teratur.</h2>
        <p class="text-indigo-200 text-sm leading-relaxed">Platform kolaborasi antara kontributor, editor, dan pimpinan untuk menghasilkan konten informasi publik yang berkualitas.</p>
      </div>
      <div class="flex items-center gap-3 text-indigo-200 text-xs">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Sistem Pendukung PPID Bappelitbangda Mahulu &copy; {{ new Date().getFullYear() }}
      </div>
    </div>

    <!-- Right form panel -->
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
      <div class="w-full max-w-sm">
        <!-- Mobile brand -->
        <div class="flex items-center gap-2 mb-8 lg:hidden">
          <img src="/image/logo_mahulu.png" alt="Logo Mahulu" class="w-9 h-9 object-contain" />
          <span class="text-slate-800 font-bold text-sm">Pendukung PPID Bappelitbangda Mahulu</span>
        </div>

        <h1 class="text-2xl font-bold text-slate-900 mb-1">Selamat datang</h1>
        <p class="text-slate-500 text-sm mb-8">Masuk untuk melanjutkan ke dashboard Anda.</p>

        <!-- Error -->
        <div v-if="errors.email" class="flex items-center gap-2 bg-red-50 border border-red-100 text-red-600 rounded-lg px-4 py-3 text-sm mb-5">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ errors.email }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 tracking-wide uppercase">Email</label>
            <input v-model="form.email" type="email" required autocomplete="email"
              class="w-full border border-slate-200 bg-white rounded-xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none"
              placeholder="nama@email.com" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 tracking-wide uppercase">Password</label>
            <input v-model="form.password" type="password" required autocomplete="current-password"
              class="w-full border border-slate-200 bg-white rounded-xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none"
              placeholder="••••••••" />
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.remember" type="checkbox" id="remember" class="rounded border-slate-300 text-indigo-600" />
            <label for="remember" class="text-sm text-slate-500">Ingat saya</label>
          </div>
          <button type="submit" :disabled="form.processing"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition disabled:opacity-50 text-sm shadow-sm shadow-indigo-200">
            {{ form.processing ? 'Memproses...' : 'Masuk →' }}
          </button>
        </form>

        <!-- hidden 
         smebunyikan untuk sementara, karena belum ada fitur demo akun -->
        <div class="mt-8 p-4 bg-slate-100 rounded-xl border border-slate-200 hidden">
          <p class="text-xs font-semibold text-slate-400 mb-3 uppercase tracking-wide">Demo Akun</p>
          <div class="space-y-2 text-xs text-slate-600">
            <div class="flex justify-between items-center">
              <span class="text-slate-400">Kontributor</span>
              <button @click="fillCredentials('contributor@ppid.local')" class="text-indigo-600 hover:text-indigo-800 font-medium">contributor@ppid.local</button>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-400">Editor</span>
              <button @click="fillCredentials('editor@ppid.local')" class="text-indigo-600 hover:text-indigo-800 font-medium">editor@ppid.local</button>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-400">Pimpinan</span>
              <button @click="fillCredentials('leader@ppid.local')" class="text-indigo-600 hover:text-indigo-800 font-medium">leader@ppid.local</button>
            </div>
            <p class="text-slate-400 pt-1">Password: <code class="bg-slate-200 px-1 rounded">password</code></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: false });
const errors = form.errors;

function fillCredentials(email) {
  form.email = email;
  form.password = 'password';
}

function submit() {
  form.post('/login');
}
</script>
