<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Status Seluruh OPD</h1>
          <p class="text-slate-400 text-xs mt-0.5">Ringkasan artikel & kontributor per OPD (lintas OPD, khusus editor & pimpinan)</p>
        </div>
        <Link href="/dashboard" class="text-xs text-indigo-600 hover:underline">&larr; Kembali ke Dashboard</Link>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="text-2xl font-bold text-slate-900">{{ opds.length }}</div>
          <div class="text-xs text-slate-400 mt-0.5">OPD Aktif</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="text-2xl font-bold text-slate-900">{{ totalArticles }}</div>
          <div class="text-xs text-slate-400 mt-0.5">Total Artikel</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="text-2xl font-bold text-emerald-600">{{ totalPublished }}</div>
          <div class="text-xs text-slate-400 mt-0.5">Dipublikasikan</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
          <div class="text-2xl font-bold text-amber-600">{{ totalPending }}</div>
          <div class="text-xs text-slate-400 mt-0.5">Menunggu Review</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Rincian per OPD</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                <th class="py-2 pr-3">OPD</th>
                <th class="py-2 px-2 text-center">Kontributor</th>
                <th class="py-2 px-2 text-center">Draft</th>
                <th class="py-2 px-2 text-center">Submitted</th>
                <th class="py-2 px-2 text-center">Returned</th>
                <th class="py-2 px-2 text-center">Approved</th>
                <th class="py-2 px-2 text-center">Published</th>
                <th class="py-2 pl-2 text-center">Total</th>
                <th class="py-2 pl-2 text-center">Progres Publikasi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="opd in opds" :key="opd.id" class="border-b border-slate-50 hover:bg-slate-50">
                <td class="py-2 pr-3 font-medium text-slate-800">{{ opd.name }}</td>
                <td class="py-2 px-2 text-center text-slate-500">{{ opd.contributors_count }}</td>
                <td class="py-2 px-2 text-center text-slate-500">{{ opd.draft_count }}</td>
                <td class="py-2 px-2 text-center text-amber-600">{{ opd.submitted_count }}</td>
                <td class="py-2 px-2 text-center text-rose-600">{{ opd.returned_count }}</td>
                <td class="py-2 px-2 text-center text-indigo-600">{{ opd.approved_count }}</td>
                <td class="py-2 px-2 text-center text-emerald-600">{{ opd.published_count }}</td>
                <td class="py-2 pl-2 text-center font-semibold text-slate-800">{{ opd.total_count }}</td>
                <td class="py-2 pl-2">
                  <div class="h-2 rounded-full bg-slate-100 overflow-hidden w-32 mx-auto">
                    <div class="h-full bg-emerald-500 rounded-full" :style="{ width: `${progressPercent(opd)}%` }" />
                  </div>
                </td>
              </tr>
              <tr v-if="!opds.length">
                <td colspan="9" class="py-6 text-center text-slate-400">Belum ada data OPD.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  opds: { type: Array, default: () => [] },
});

const totalArticles = computed(() => props.opds.reduce((sum, opd) => sum + (opd.total_count || 0), 0));
const totalPublished = computed(() => props.opds.reduce((sum, opd) => sum + (opd.published_count || 0), 0));
const totalPending = computed(() => props.opds.reduce((sum, opd) => sum + (opd.submitted_count || 0), 0));

function progressPercent(opd) {
  if (!opd.total_count) return 0;
  return Math.round((opd.published_count / opd.total_count) * 100);
}
</script>
