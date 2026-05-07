<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link href="/editor/inbox" class="text-gray-400 hover:text-gray-600">←</Link>
        <div class="flex-1">
          <h1 class="text-xl font-bold text-gray-900 truncate">{{ article.title }}</h1>
          <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
            <span>oleh {{ article.author?.name }}</span>
            &bull;
            <span>{{ article.category?.name ?? 'Tanpa Kategori' }}</span>
            &bull;
            <span :class="statusClass">{{ article.status }}</span>
          </div>
        </div>
        <a :href="`/preview/${article.preview_token}`" target="_blank"
          class="text-xs bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">
          ↗ Preview
        </a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Article Content -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Content Tabs -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex border-b">
              <button v-for="tab in ['Konten Web', 'Instagram', 'SEO']" :key="tab"
                @click="activeTab = tab"
                class="px-4 py-3 text-sm font-medium transition"
                :class="activeTab === tab ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'">
                {{ tab }}
              </button>
            </div>

            <div class="p-6">
              <!-- Web Content -->
              <div v-show="activeTab === 'Konten Web'">
                <div v-if="article.excerpt" class="bg-gray-50 rounded-lg p-3 mb-4 text-sm text-gray-600 italic">
                  {{ article.excerpt }}
                </div>
                <div class="prose max-w-none text-sm leading-relaxed">
                  <div v-if="article.body_web" v-html="renderedContent"></div>
                  <p v-else class="text-gray-400">Tidak ada konten web.</p>
                </div>
              </div>

              <!-- IG Content -->
              <div v-show="activeTab === 'Instagram'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                  <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Caption</p>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ article.caption_ig || 'Tidak ada caption' }}</p>
                  </div>
                  <div v-if="article.hashtags_ig">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Hashtags</p>
                    <p class="text-xs text-blue-600">{{ article.hashtags_ig }}</p>
                  </div>
                </div>
                <IGPreviewCard :caption="article.caption_ig" :hashtags="article.hashtags_ig" :media="article.media" />
              </div>

              <!-- SEO Content -->
              <div v-show="activeTab === 'SEO'">
                <div v-if="article.seo" class="space-y-3 text-sm">
                  <InfoRow label="SEO Title" :value="article.seo.seo_title" />
                  <InfoRow label="Meta Description" :value="article.seo.seo_description" />
                  <InfoRow label="Keywords" :value="article.seo.seo_keywords" />
                  <InfoRow label="OG Title" :value="article.seo.og_title" />
                  <InfoRow label="Canonical URL" :value="article.seo.canonical_url" />
                  <InfoRow label="Robots" :value="`index: ${article.seo.robots_index ? 'yes' : 'no'}, follow: ${article.seo.robots_follow ? 'yes' : 'no'}`" />
                </div>
                <p v-else class="text-sm text-gray-400">Tidak ada data SEO.</p>
              </div>
            </div>
          </div>

          <!-- Activity Log -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Riwayat Aktivitas</h3>
            <div class="space-y-2">
              <div v-for="log in article.activity_logs" :key="log.id"
                class="flex items-start gap-3 text-xs">
                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold flex-shrink-0 text-xs">
                  {{ log.user?.name?.charAt(0) }}
                </div>
                <div>
                  <span class="font-medium">{{ log.user?.name }}</span>
                  <span class="text-gray-500"> — {{ log.action }}</span>
                  <div v-if="log.notes" class="text-gray-500 mt-0.5">{{ log.notes }}</div>
                  <div class="text-gray-400 mt-0.5">{{ formatDate(log.created_at) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Review Panel -->
        <div class="space-y-4">
          <!-- Approve -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <h3 class="font-semibold text-gray-900 mb-3">Tindakan Editor</h3>
            <div class="space-y-3">
              <button @click="approve"
                class="w-full bg-green-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                ✅ Setujui & Publikasikan
              </button>

              <div class="pt-2 border-t border-gray-100">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Kembalikan ke Kontributor</label>
                <textarea v-model="returnNotes" rows="4" placeholder="Tulis catatan/alasan pengembalian..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-400"
                  :class="{ 'border-red-300': returnError }"></textarea>
                <p v-if="returnError" class="text-red-500 text-xs mt-1">{{ returnError }}</p>
                <button @click="returnArticle"
                  class="mt-2 w-full bg-red-50 text-red-700 py-2 rounded-lg text-sm font-medium hover:bg-red-100 border border-red-200 transition">
                  ↩ Kembalikan
                </button>
              </div>
            </div>
          </div>

          <!-- Article Info -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
            <h3 class="font-semibold text-gray-900 text-sm">Info Artikel</h3>
            <InfoRow label="Platform" :value="article.target_platform" small />
            <InfoRow label="Dibuat" :value="formatDate(article.created_at)" small />
            <InfoRow label="Diperbarui" :value="formatDate(article.updated_at)" small />
          </div>
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
  submitted: 'text-yellow-600',
  returned: 'text-red-600',
  approved: 'text-blue-600',
  published: 'text-green-600',
}[props.article.status] ?? 'text-gray-600'));

const renderedContent = computed(() => {
  if (!props.article.body_web) return '';
  // Simple render from TipTap JSON
  return renderNodes(props.article.body_web?.content ?? []);
});

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
      case 'blockquote': return `<blockquote class="border-l-4 border-blue-300 pl-4 italic text-gray-600 mb-3">${inner}</blockquote>`;
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
      <span :class="small ? 'text-xs text-gray-700' : 'text-sm text-gray-700'" class="text-right">{{ value || '-' }}</span>
    </div>
  `,
};
</script>
