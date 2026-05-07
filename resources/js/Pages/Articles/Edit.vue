<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link href="/articles" class="text-gray-400 hover:text-gray-600">←</Link>
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-gray-900">Edit Artikel</h1>
          <div class="flex items-center gap-2 mt-1">
            <StatusBadge :status="article.status" />
            <a :href="`/preview/${article.preview_token}`" target="_blank"
              class="text-xs text-blue-600 hover:underline">↗ Preview</a>
          </div>
        </div>
      </div>

      <!-- Editor Notes Warning -->
      <div v-if="article.status === 'returned' && article.editor_notes"
        class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-amber-800 mb-1">📝 Catatan Editor:</h3>
        <p class="text-sm text-amber-700">{{ article.editor_notes }}</p>
      </div>

      <form @submit.prevent="save" class="space-y-6">
        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
          <h2 class="font-semibold text-gray-900">Informasi Dasar</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel *</label>
            <input v-model="form.title" type="text" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Judul artikel..." />
            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
              <select v-model="form.category_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Pilih kategori...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
              <select v-model="form.target_platform"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="web">Web</option>
                <option value="ig">Instagram</option>
                <option value="both">Keduanya</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Thumbnail Baru</label>
              <input type="file" accept="image/*" @change="handleThumbnail"
                class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              <p v-if="article.thumbnail" class="text-xs text-gray-400 mt-1">Sudah ada thumbnail</p>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
            <textarea v-model="form.excerpt" rows="2"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
              placeholder="Ringkasan singkat..."></textarea>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <ArticleFormTabs>
            <template #web>
              <TiptapEditor v-model="form.body_web" />
            </template>
            <template #ig>
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption IG</label>
                    <textarea v-model="form.caption_ig" rows="6"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                      placeholder="Caption Instagram..."></textarea>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hashtags</label>
                    <textarea v-model="form.hashtags_ig" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                      placeholder="#ppid #informasipublik"></textarea>
                  </div>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-700 mb-2">Preview Instagram</p>
                  <IGPreviewCard :caption="form.caption_ig" :hashtags="form.hashtags_ig" :media="article.media" />
                </div>
              </div>
            </template>
            <template #seo>
              <SeoTab v-model="form.seo" />
            </template>
          </ArticleFormTabs>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 pb-6">
          <button type="submit" :disabled="form.processing"
            class="bg-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-700 transition disabled:opacity-50">
            💾 Simpan
          </button>
          <button v-if="['draft', 'returned'].includes(article.status)"
            type="button" @click="submitArticle" :disabled="form.processing"
            class="bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition disabled:opacity-50">
            📤 Simpan & Submit
          </button>
          <Link href="/articles" class="text-gray-500 hover:text-gray-700 text-sm">Batal</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ArticleFormTabs from '@/Components/ArticleFormTabs.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';
import SeoTab from '@/Components/SeoTab.vue';
import IGPreviewCard from '@/Components/IGPreviewCard.vue';
import { Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  article: Object,
  categories: Array,
});

const form = useForm({
  title: props.article.title,
  body_web: props.article.body_web,
  excerpt: props.article.excerpt ?? '',
  thumbnail: null,
  caption_ig: props.article.caption_ig ?? '',
  hashtags_ig: props.article.hashtags_ig ?? '',
  target_platform: props.article.target_platform ?? 'web',
  category_id: props.article.category_id ?? '',
  seo: {
    seo_title: props.article.seo?.seo_title ?? '',
    seo_description: props.article.seo?.seo_description ?? '',
    seo_keywords: props.article.seo?.seo_keywords ?? '',
    og_title: props.article.seo?.og_title ?? '',
    og_description: props.article.seo?.og_description ?? '',
    og_image: props.article.seo?.og_image ?? '',
    canonical_url: props.article.seo?.canonical_url ?? '',
    robots_index: props.article.seo?.robots_index ?? true,
    robots_follow: props.article.seo?.robots_follow ?? true,
  },
});

function handleThumbnail(e) {
  form.thumbnail = e.target.files[0];
}

function save() {
  form.put(`/articles/${props.article.id}`);
}

function submitArticle() {
  save();
  router.post(`/articles/${props.article.id}/submit`);
}

const StatusBadge = {
  props: ['status'],
  computed: {
    classes() {
      const map = {
        draft: 'bg-gray-100 text-gray-600',
        submitted: 'bg-yellow-100 text-yellow-700',
        returned: 'bg-red-100 text-red-700',
        approved: 'bg-blue-100 text-blue-700',
        published: 'bg-green-100 text-green-700',
      };
      return map[this.status] ?? 'bg-gray-100 text-gray-600';
    },
  },
  template: `<span class="text-xs px-2.5 py-0.5 rounded-full font-medium" :class="classes">{{ status }}</span>`,
};
</script>
