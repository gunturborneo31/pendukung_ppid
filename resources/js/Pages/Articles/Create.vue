<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link href="/articles" class="text-gray-400 hover:text-gray-600">←</Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Buat Artikel Baru</h1>
          <p class="text-gray-500 text-sm">Isi semua tab sebelum submit</p>
        </div>
      </div>

      <form @submit.prevent="save('draft')" class="space-y-6">
        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
          <h2 class="font-semibold text-gray-900">Informasi Dasar</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel *</label>
            <input v-model="form.title" type="text" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Tulis judul artikel yang menarik..." />
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Thumbnail</label>
              <input type="file" accept="image/*" @change="handleThumbnail"
                class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt/Ringkasan</label>
            <textarea v-model="form.excerpt" rows="2"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
              placeholder="Ringkasan singkat artikel..."></textarea>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <ArticleFormTabs>
            <!-- Web Tab -->
            <template #web>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Konten Web</label>
                <TiptapEditor v-model="form.body_web" />
              </div>
            </template>

            <!-- IG Tab -->
            <template #ig>
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Caption Instagram <span class="text-xs text-gray-400">{{ (form.caption_ig?.length ?? 0) }}/2200</span>
                    </label>
                    <textarea v-model="form.caption_ig" rows="6"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                      placeholder="Tulis caption menarik untuk Instagram..."></textarea>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hashtags</label>
                    <textarea v-model="form.hashtags_ig" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                      placeholder="#ppid #informasipublik #berita"></textarea>
                  </div>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-700 mb-2">Preview Instagram</p>
                  <IGPreviewCard :caption="form.caption_ig" :hashtags="form.hashtags_ig" />
                </div>
              </div>
            </template>

            <!-- SEO Tab -->
            <template #seo>
              <SeoTab v-model="form.seo" />
            </template>
          </ArticleFormTabs>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 pb-6">
          <button type="submit" :disabled="form.processing"
            class="bg-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-700 transition disabled:opacity-50">
            💾 Simpan Draft
          </button>
          <button type="button" @click="save('submitted')" :disabled="form.processing"
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
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
  categories: Array,
});

const form = useForm({
  title: '',
  body_web: null,
  excerpt: '',
  thumbnail: null,
  caption_ig: '',
  hashtags_ig: '',
  status: 'draft',
  target_platform: 'web',
  category_id: '',
  seo: {
    seo_title: '', seo_description: '', seo_keywords: '',
    og_title: '', og_description: '', og_image: '', canonical_url: '',
    robots_index: true, robots_follow: true,
  },
});

function handleThumbnail(e) {
  form.thumbnail = e.target.files[0];
}

function save(status) {
  form.status = status;
  form.post('/articles');
}
</script>
