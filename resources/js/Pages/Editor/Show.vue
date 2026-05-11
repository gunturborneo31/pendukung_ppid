<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
       <Link href="/editor/inbox" class="inline-flex items-center gap-1 text-slate-400 hover:text-slate-600 text-xs">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Inbox
        </Link>
      <div class="flex items-center gap-4">
        
        <div class="flex-1">
          <h1 class="text-xl font-bold text-slate-900 truncate">{{ article.title }}</h1>
          <div class="flex items-center gap-2 mt-1 text-xs text-slate-400">
            <span>oleh {{ article.author?.name }}</span>
            &bull;
            <span>{{ article.category?.name ?? 'Tanpa Kategori' }}</span>
            <span v-if="article.editor">
              &bull;
              <span>editor: <strong>{{ article.editor.name }}</strong></span>
              <span v-if="article.editor.field">({{ article.editor.field }})</span>
            </span>
            &bull;
            <span :class="statusClass">{{ article.status }}</span>
          </div>
        </div>
        <a :href="`/preview/${article.preview_token}`" target="_blank"
          class="text-xs border border-slate-200 text-slate-600 px-3 py-1.5 rounded-lg hover:bg-slate-50 transition">
          ↗ Preview
        </a>
        <Link :href="`/editor/articles/${article.id}/edit-full`"
          class="text-xs border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition">
          Edit Lengkap
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Article Content -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Content Tabs -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex border-b border-slate-100">
              <button v-for="tab in ['Konten Web', 'Instagram']" :key="tab"
                @click="activeTab = tab"
                class="px-5 py-3 text-sm font-medium transition"
                :class="activeTab === tab ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-400 hover:text-slate-700'">
                {{ tab }}
              </button>
            </div>

            <div class="p-6">
              <!-- Web Content -->
              <div v-show="activeTab === 'Konten Web'">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                  <div v-if="thumbnailUrl" class="mb-5 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                    <img :src="thumbnailUrl" :alt="article.title" class="h-auto w-full object-cover" />
                  </div>

                  <h3 class="text-sm font-semibold text-slate-700 mb-3">Isi Berita</h3>
                  <div class="prose max-w-none text-sm leading-relaxed">
                    <div v-if="webContentHtml" v-html="webContentHtml"></div>
                    <p v-else class="text-gray-400">Tidak ada konten web.</p>
                  </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                  <h2 class="text-base font-semibold text-slate-800 mb-4">Urutan Input Kontributor</h2>
                  <div class="space-y-4 text-sm">
                    <div>
                      <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Judul Artikel</p>
                      <p class="font-semibold text-slate-900">{{ article.title || '-' }}</p>
                    </div>

                    <div>
                      <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">SEO Title</p>
                      <p class="text-slate-700 whitespace-pre-wrap">{{ article.seo?.seo_title || '-' }}</p>
                    </div>

                    <div>
                      <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Deskripsi</p>
                      <p class="text-slate-700 whitespace-pre-wrap">{{ article.excerpt || article.seo?.seo_description || '-' }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Kategori</p>
                        <p class="text-slate-700">{{ article.category?.name ?? 'Tanpa Kategori' }}</p>
                      </div>
                      <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tanggal Artikel</p>
                        <p class="text-slate-700">{{ formatDate(article.published_at || article.created_at) }}</p>
                      </div>
                    </div>

                    <div>
                      <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tags</p>
                      <div v-if="seoKeywordsList.length" class="flex flex-wrap gap-2">
                        <span v-for="(tag, idx) in seoKeywordsList" :key="`tag-${idx}`" class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                          {{ tag }}
                        </span>
                      </div>
                      <p v-else class="text-slate-500">-</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- IG Content -->
              <div v-show="activeTab === 'Instagram'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <IGPreviewCard :caption="article.caption_ig" :hashtags="article.hashtags_ig" :media="instagramMedia" />
                <div class="space-y-3">
                  <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Caption</p>
                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ article.caption_ig || 'Tidak ada caption' }}</p>
                  </div>
                  <div v-if="article.hashtags_ig">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Hashtags</p>
                    <p class="text-xs text-indigo-600">{{ article.hashtags_ig }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Activity Log -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Riwayat Aktivitas</h3>
            <div class="space-y-3">
              <div v-for="log in article.activity_logs" :key="log.id"
                class="flex items-start gap-3 text-xs">
                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold flex-shrink-0 text-xs">
                  {{ log.user?.name?.charAt(0) }}
                </div>
                <div>
                  <span class="font-medium text-slate-700">{{ log.user?.name }}</span>
                  <span class="text-slate-400"> — {{ log.action }}</span>
                  <div v-if="log.notes" class="text-slate-500 mt-0.5">{{ log.notes }}</div>
                  <div class="text-slate-400 mt-0.5">{{ formatDate(log.created_at) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Review Panel -->
        <div class="space-y-4">
          <!-- Approve -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-semibold text-slate-900 mb-4 text-sm">Tindakan Editor</h3>
            <div class="space-y-3">
              <button @click="approve"
                class="w-full bg-emerald-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-emerald-700 transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Setujui & Publikasikan
              </button>

              <Link :href="`/editor/articles/${article.id}/edit-full`"
                class="w-full inline-flex items-center justify-center gap-2 border border-indigo-200 text-indigo-700 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-50 transition">
                Edit Lengkap
              </Link>

              <div class="pt-3 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Kembalikan ke Kontributor</label>
                <textarea v-model="returnNotes" rows="4" placeholder="Tulis catatan/alasan pengembalian..."
                  class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-400 outline-none transition resize-none"
                  :class="{ 'border-red-300': returnError }"></textarea>
                <p v-if="returnError" class="text-red-500 text-xs mt-1">{{ returnError }}</p>
                <button @click="returnArticle"
                  class="mt-2 w-full bg-red-50 text-red-700 py-2 rounded-xl text-sm font-medium hover:bg-red-100 border border-red-200 transition">
                  ↩ Kembalikan
                </button>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Info Artikel</h3>
            <InfoRow label="Platform" :value="article.target_platform" small />
            <InfoRow label="Dibuat" :value="formatDate(article.created_at)" small />
            <InfoRow label="Diperbarui" :value="formatDate(article.updated_at)" small />
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">File Pendukung</h3>
            <div v-if="supportingFiles.length" class="space-y-3">
              <div v-for="file in supportingFiles" :key="file.id" class="rounded-xl border border-slate-200 p-3">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-800 truncate">{{ fileName(file.path) }}</p>
                    <p class="text-[11px] text-slate-500">{{ file.type || 'file' }} • {{ fileSize(file.size) }}</p>
                    <p v-if="file.description" class="text-[11px] text-slate-500 mt-1 whitespace-pre-wrap">{{ file.description }}</p>
                  </div>
                  <div class="flex flex-col items-end gap-1">
                    <a :href="fileUrl(file.path)" target="_blank" class="text-[11px] text-indigo-600 hover:text-indigo-800">Lihat</a>
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-xs text-slate-500">Belum ada file pendukung.</p>
          </div>

          <!-- <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Media Instagram</h3>
            <div v-if="instagramMedia.length" class="space-y-3">
              <div v-for="file in instagramMedia" :key="`ig-${file.id}`" class="rounded-xl border border-slate-200 p-3">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-800 truncate">{{ fileName(file.path) }}</p>
                    <p class="text-[11px] text-slate-500">{{ file.type || 'media' }} • {{ fileSize(file.size) }}</p>
                  </div>
                  <div class="flex flex-col items-end gap-1">
                    <a :href="fileUrl(file.path)" target="_blank" class="text-[11px] text-indigo-600 hover:text-indigo-800">Lihat</a>
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-xs text-slate-500">Belum ada media Instagram.</p>
          </div> -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import IGPreviewCard from '@/Components/IGPreviewCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ article: Object });

const activeTab = ref('Konten Web');
const returnNotes = ref('');
const returnError = ref('');

const statusClass = computed(() => ({
  submitted: 'text-amber-600',
  returned: 'text-red-600',
  approved: 'text-indigo-600',
  published: 'text-emerald-600',
}[props.article.status] ?? 'text-slate-500'));

const webContentHtml = computed(() => {
  if (props.article?.body_web_html) {
    return props.article.body_web_html;
  }

  const body = props.article.body_web;
  if (!body) return '';

  // If saved as raw HTML string, render directly.
  if (typeof body === 'string') return body;

  // If saved as TipTap JSON, transform nodes to HTML.
  if (typeof body === 'object' && Array.isArray(body.content)) {
    return renderNodes(body.content);
  }

  return '';
});

const thumbnailUrl = computed(() => {
  const thumb = props.article?.thumbnail;
  if (!thumb) return '';
  if (thumb.startsWith('http://') || thumb.startsWith('https://') || thumb.startsWith('/')) return thumb;
  return `/storage/${thumb}`;
});

const seoKeywordsList = computed(() => {
  const keywords = props.article?.seo?.seo_keywords;
  if (!keywords) return [];

  if (Array.isArray(keywords)) {
    return keywords
      .map((k) => (typeof k === 'string' ? k : k?.text))
      .filter(Boolean);
  }

  if (typeof keywords === 'string') {
    return keywords
      .split(',')
      .map((k) => k.trim())
      .filter(Boolean);
  }

  return [];
});

const instagramMedia = computed(() => {
  const allMedia = Array.isArray(props.article?.media) ? props.article.media : [];
  const tagged = allMedia.filter((m) => m?.alt_text === 'ig_media');

  // Backward compatibility: old data may not have alt_text marker.
  if (tagged.length > 0) return tagged;

  return allMedia.filter((m) => {
    if (!m) return false;
    if (m.type === 'image' || m.type === 'video') return true;
    const source = `${m.path || m.url || ''}`.toLowerCase();
    return /\.(jpg|jpeg|png|gif|webp|mp4|mov|webm|ogg)$/.test(source);
  });
});

const supportingFiles = computed(() => {
  const allMedia = Array.isArray(props.article?.media) ? props.article.media : [];
  return allMedia.filter((m) => m?.alt_text !== 'ig_media');
});

function fileUrl(path) {
  if (!path) return '#';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) return path;
  return `/storage/${path}`;
}

function fileName(path) {
  if (!path) return '-';
  const parts = path.split('/');
  return parts[parts.length - 1] || path;
}

function fileSize(size) {
  if (!size || Number.isNaN(Number(size))) return '-';
  const kb = Number(size) / 1024;
  if (kb < 1024) return `${Math.round(kb)} KB`;
  return `${(kb / 1024).toFixed(1)} MB`;
}

function renderNodes(nodes) {
  return nodes.map(node => {
    const inner = renderNodes(node.content ?? []);
    const text = node.marks?.reduce((t, mark) => {
      if (mark.type === 'bold') return `<strong>${t}</strong>`;
      if (mark.type === 'italic') return `<em>${t}</em>`;
      if (mark.type === 'link') return `<a href="${mark.attrs?.href}" target="_blank" class="text-blue-600 underline">${t}</a>`;
      return t;
    }, node.text ?? '') ?? '';

    switch (node.type) {
      case 'paragraph': return `<p class="mb-3">${inner}${text}</p>`;
      case 'heading': return `<h${node.attrs?.level} class="font-bold mt-4 mb-2">${inner}${text}</h${node.attrs?.level}>`;
      case 'bulletList': return `<ul class="list-disc pl-5 mb-3">${inner}</ul>`;
      case 'orderedList': return `<ol class="list-decimal pl-5 mb-3">${inner}</ol>`;
      case 'listItem': return `<li>${inner}${text}</li>`;
      case 'blockquote': return `<blockquote class="border-l-4 border-indigo-300 pl-4 italic text-slate-500 mb-3">${inner}</blockquote>`;
      case 'text': return text;
      default: return inner + text;
    }
  }).join('');
}

function approve() {
  if (confirm('Yakin ingin menyetujui dan mempublikasikan artikel ini?')) {
    router.post(`/editor/articles/${props.article.id}/approve`);
  }
}

function returnArticle() {
  if (!returnNotes.value.trim()) {
    returnError.value = 'Catatan pengembalian harus diisi.';
    return;
  }
  returnError.value = '';
  router.post(`/editor/articles/${props.article.id}/return`, {
    editor_notes: returnNotes.value,
  });
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const InfoRow = {
  props: ['label', 'value', 'small'],
  template: `
    <div class="flex justify-between items-start gap-2">
      <span class="text-xs text-gray-500 shrink-0">{{ label }}</span>
      <span :class="small ? 'text-xs text-gray-700' : 'text-sm text-gray-700'" class="    text-right">{{ value || '-' }}</span>
    </div>
  `,
};
</script>
