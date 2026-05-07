<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-900 to-blue-700 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="text-4xl mb-3">📋</div>
        <h1 class="text-2xl font-bold text-gray-900">Pendukung PPID</h1>
        <p class="text-gray-500 text-sm mt-1">Sistem Manajemen Konten PPID</p>
      </div>

      <!-- Error -->
      <div v-if="errors.email" class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-4">
        {{ errors.email }}
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="form.email" type="email" required autocomplete="email"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            placeholder="email@ppid.local" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="form.password" type="password" required autocomplete="current-password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            placeholder="••••••••" />
        </div>
        <div class="flex items-center gap-2">
          <input v-model="form.remember" type="checkbox" id="remember" class="rounded" />
          <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
        </div>
        <button type="submit" :disabled="form.processing"
          class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2.5 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
          {{ form.processing ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>

      <!-- Demo credentials -->
      <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <p class="text-xs font-semibold text-gray-500 mb-2">Demo Akun:</p>
        <div class="space-y-1 text-xs text-gray-600">
          <div class="flex justify-between">
            <span>Kontributor:</span>
            <button @click="fillCredentials('contributor@ppid.local')" class="text-blue-600 hover:underline">contributor@ppid.local</button>
          </div>
          <div class="flex justify-between">
            <span>Editor:</span>
            <button @click="fillCredentials('editor@ppid.local')" class="text-blue-600 hover:underline">editor@ppid.local</button>
          </div>
          <div class="flex justify-between">
            <span>Pimpinan:</span>
            <button @click="fillCredentials('leader@ppid.local')" class="text-blue-600 hover:underline">leader@ppid.local</button>
          </div>
          <div class="mt-1 text-gray-400">Password: <code>password</code></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const errors = form.errors;

function fillCredentials(email) {
  form.email = email;
  form.password = 'password';
}

function submit() {
  form.post('/login');
}
</script>
