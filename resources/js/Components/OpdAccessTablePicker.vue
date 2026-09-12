<template>
  <div class="space-y-3">
    <div class="flex flex-col gap-2">
      <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
        <label class="text-xs text-slate-500 shrink-0">Filter kolom:</label>
        <select
          v-model="filterColumn"
          class="border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
        >
          <option value="all">Semua Kolom</option>
          <option value="name">Nama</option>
          <option value="code">Kode</option>
          <option value="daerah">Daerah</option>
        </select>
        <input
          v-model="search"
          type="text"
          class="w-full sm:max-w-sm border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
          :placeholder="searchPlaceholder"
        />
      </div>
      <div class="flex items-center gap-2 text-xs">
        <label class="text-slate-500">Urutkan:</label>
        <select
          v-model="sortKey"
          class="border border-slate-200 rounded-lg px-2 py-1.5 text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
        >
          <option value="name">Nama</option>
          <option value="code">Kode</option>
          <option value="daerah">Daerah</option>
        </select>
        <button
          type="button"
          @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
          class="border border-slate-200 rounded-lg px-2 py-1.5 text-slate-700 hover:bg-slate-50"
        >
          {{ sortDirection === 'asc' ? 'A-Z' : 'Z-A' }}
        </button>
      </div>
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
      <input
        type="checkbox"
        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-200"
        :checked="allSelected"
        @change="toggleSelectAll($event.target.checked)"
      />
      Pilih Semua OPD
    </label>

    <div class="border border-slate-200 rounded-xl overflow-hidden">
      <div class="max-h-64 overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-100 sticky top-0">
            <tr class="text-left text-[11px] uppercase tracking-wide text-slate-500">
              <th class="px-3 py-2 w-12">Pilih</th>
              <th class="px-3 py-2">Nama OPD</th>
              <th class="px-3 py-2">Kode</th>
              <th class="px-3 py-2">Daerah</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="opd in paginatedOpds" :key="opd.id" class="border-b border-slate-100 hover:bg-slate-50">
              <td class="px-3 py-2">
                <input
                  type="checkbox"
                  :checked="selectedSet.has(Number(opd.id))"
                  class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-200"
                  @change="toggleSelection(opd.id)"
                />
              </td>
              <td class="px-3 py-2 text-slate-800">{{ opd.name }}</td>
              <td class="px-3 py-2 text-slate-500">{{ opd.code ?? '-' }}</td>
              <td class="px-3 py-2 text-slate-500">{{ opd.daerah ?? '-' }}</td>
            </tr>
            <tr v-if="!paginatedOpds.length">
              <td colspan="4" class="px-3 py-6 text-center text-slate-400 text-xs">OPD tidak ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="flex items-center justify-between text-xs text-slate-500">
      <span>Menampilkan {{ pageStartRow }}-{{ pageEndRow }} dari {{ sortedFilteredOpds.length }} OPD</span>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="border border-slate-200 rounded-lg px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
          :disabled="currentPage <= 1"
          @click="currentPage -= 1"
        >
          Prev
        </button>
        <span>Hal. {{ currentPage }} / {{ totalPages }}</span>
        <button
          type="button"
          class="border border-slate-200 rounded-lg px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
          :disabled="currentPage >= totalPages"
          @click="currentPage += 1"
        >
          Next
        </button>
      </div>
    </div>

    <p class="text-xs text-slate-500">Terpilih: {{ selectedIds.length }} OPD</p>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  opds: { type: Array, default: () => [] },
  modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const search = ref('');
const filterColumn = ref('all');
const sortKey = ref('name');
const sortDirection = ref('asc');
const currentPage = ref(1);
const pageSize = 10;

const selectedIds = computed(() => {
  return (props.modelValue ?? []).map((id) => Number(id)).filter((id) => Number.isFinite(id));
});

const selectedSet = computed(() => new Set(selectedIds.value));
const allOpdIds = computed(() => {
  return props.opds.map((opd) => Number(opd.id)).filter((id) => Number.isFinite(id));
});
const allSelected = computed(() => {
  return allOpdIds.value.length > 0 && allOpdIds.value.every((id) => selectedSet.value.has(id));
});

const searchPlaceholder = computed(() => {
  if (filterColumn.value === 'name') return 'Cari nama OPD...';
  if (filterColumn.value === 'code') return 'Cari kode OPD...';
  if (filterColumn.value === 'daerah') return 'Cari daerah OPD...';
  return 'Cari nama/kode/daerah OPD...';
});

const sortedFilteredOpds = computed(() => {
  const query = search.value.trim().toLowerCase();
  const filtered = props.opds.filter((opd) => {
    if (!query) return true;
    if (filterColumn.value === 'name') return String(opd.name ?? '').toLowerCase().includes(query);
    if (filterColumn.value === 'code') return String(opd.code ?? '').toLowerCase().includes(query);
    if (filterColumn.value === 'daerah') return String(opd.daerah ?? '').toLowerCase().includes(query);

    const source = `${opd.name ?? ''} ${opd.code ?? ''} ${opd.daerah ?? ''}`.toLowerCase();
    return source.includes(query);
  });

  return filtered.slice().sort((a, b) => {
    const left = String(a[sortKey.value] ?? '').toLowerCase();
    const right = String(b[sortKey.value] ?? '').toLowerCase();
    if (left === right) return 0;
    const result = left < right ? -1 : 1;
    return sortDirection.value === 'asc' ? result : -result;
  });
});

const totalPages = computed(() => {
  const total = Math.ceil(sortedFilteredOpds.value.length / pageSize);
  return total > 0 ? total : 1;
});

const paginatedOpds = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return sortedFilteredOpds.value.slice(start, start + pageSize);
});

const pageStartRow = computed(() => {
  if (!sortedFilteredOpds.value.length) return 0;
  return (currentPage.value - 1) * pageSize + 1;
});

const pageEndRow = computed(() => {
  if (!sortedFilteredOpds.value.length) return 0;
  return Math.min(currentPage.value * pageSize, sortedFilteredOpds.value.length);
});

watch([search, filterColumn, sortKey, sortDirection], () => {
  currentPage.value = 1;
});

watch(totalPages, () => {
  if (currentPage.value > totalPages.value) {
    currentPage.value = totalPages.value;
  }
});

function toggleSelection(opdId) {
  const id = Number(opdId);
  const current = [...selectedIds.value];
  const idx = current.findIndex((value) => value === id);
  if (idx >= 0) {
    current.splice(idx, 1);
  } else {
    current.push(id);
  }

  emit('update:modelValue', current);
}

function toggleSelectAll(checked) {
  if (checked) {
    emit('update:modelValue', [...allOpdIds.value]);
    return;
  }

  emit('update:modelValue', []);
}
</script>
