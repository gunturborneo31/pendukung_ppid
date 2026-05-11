<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link href="/articles" class="text-gray-400 hover:text-gray-600">←</Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Edit Artikel</h1>
          <p class="text-gray-500 text-sm">Isi informasi dasar, konten, dan file pendukung. SEO & tab lainnya opsional</p>
        </div>
      </div>

      <form @submit.prevent="save('draft')" class="space-y-6 relative">
        <!-- Penilaian Artikel sticky di kanan, tidak tumpang tindih -->
        <div class="fixed top-2 right-8 z-50 hidden xl:block" style="right: max(2rem, calc((100vw-1200px)/2 - 22rem));">
          <div class="flex flex-col items-end border border-gray-200 bg-white rounded-lg p-4 w-150 shadow-xl">
            <span class="text-xs font-semibold text-gray-500 mb-1">Penilaian Artikel</span>
            <span class="text-xs text-gray-400 mb-2">(Rekomendasi, bukan persyaratan)</span>
            <div class="flex items-center gap-2 mb-2">
              <div class="w-24 bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full transition-all" :class="scoreColor" :style="{ width: seoScore + '%' }">
                </div>
              </div>
              <span class="text-sm font-bold" :class="scoreTextColor">{{ seoScoreLabel }}</span>
            </div>
            <ul class="text-xs space-y-1 text-left w-full grid grid-cols-2 gap-1">
              <li v-for="check in seoChecks" :key="check.label" :class="check.pass ? 'text-green-600' : 'text-red-500'">
                {{ check.pass ? '✓' : '✗' }} {{ check.label }}
                <span v-if="check.param !== undefined" class="ml-1 text-gray-400">[{{ check.param }}]</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Untuk layar kecil, tampilkan di bawah form -->
        <div class="block xl:hidden mt-6">
          <div class="flex flex-col items-end border border-gray-200 bg-white rounded-lg p-4 w-full shadow-xl">
            <span class="text-xs font-semibold text-gray-500 mb-1">Penilaian Artikel</span>
            <span class="text-xs text-gray-400 mb-2">(Rekomendasi, bukan persyaratan)</span>
            <div class="flex items-center gap-2 mb-2">
              <div class="w-24 bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full transition-all" :class="scoreColor" :style="{ width: seoScore + '%' }">
                </div>
              </div>
              <span class="text-sm font-bold" :class="scoreTextColor">{{ seoScoreLabel }}</span>
            </div>
            <ul class="text-xs space-y-1 text-left w-full">
              <li v-for="check in seoChecks" :key="check.label" :class="check.pass ? 'text-green-600' : 'text-red-500'">
                {{ check.pass ? '✓' : '✗' }} {{ check.label }}
                <span v-if="check.param !== undefined" class="ml-1 text-gray-400">[{{ check.param }}]</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
          <h2 class="font-semibold text-gray-900">Informasi Dasar</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel *</label>
              <input v-model="form.title" type="text" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Tulis judul artikel yang menarik..." />
              <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">SEO Title <span class="text-xs font-normal text-gray-400">(opsional)</span></label>
              <input v-model="form.seo.seo_title" type="text" maxlength="80"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Judul untuk mesin pencari..." />
              <span class="text-xs" :class="seoTitleLen > 60 ? 'text-red-500' : 'text-gray-400'">{{ seoTitleLen }}/60</span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description <span class="text-xs font-normal text-gray-400">(opsional)</span></label>
              <textarea v-model="form.seo.seo_description" rows="2" maxlength="160"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="Deskripsi singkat artikel untuk mesin pencari..."></textarea>
              <span class="text-xs"
                :class="seoDescLen > 160 ? 'text-red-500' : seoDescLen > 120 ? 'text-yellow-500' : 'text-green-500'">{{ seoDescLen }}/160</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tags <span class="text-xs font-normal text-gray-400">(opsional)</span></label>
              <TagsInput v-model="form.seo.seo_keywords" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
              <select v-model="form.category_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Pilih kategori...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Artikel *</label>
              <input v-model="form.published_at" type="date" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Tanggal artikel..." />
              <p v-if="form.errors.published_at" class="text-red-500 text-xs mt-1">{{ form.errors.published_at }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
          <h2 class="font-semibold text-gray-900">Konten Artikel</h2>
          <ArticleFormTabs>
            <template #web>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Thumbnail Artikel</label>
                  <div v-if="thumbnailPreview || article.thumbnail" class="relative mb-4">
                    <div class="w-1/2 h-56 bg-gray-100 rounded-lg overflow-hidden">
                      <img :src="thumbnailPreview || `/storage/${article.thumbnail}`" alt="Preview" class="w-full h-full object-cover" />
                    </div>
                    <div class="flex gap-2 mt-2">
                      <a v-if="article.thumbnail" :href="fileUrl(article.thumbnail)" :download="fileName(article.thumbnail)"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium cursor-pointer">
                        Unduh
                      </a>
                      <label class="text-blue-600 hover:text-blue-700 text-sm font-medium cursor-pointer">
                        Ubah
                        <input type="file" accept="image/*" class="hidden" @change="handleThumbnail" />
                      </label>
                      <button type="button" @click="deleteThumbnail"
                        class="text-red-600 hover:text-red-700 text-sm font-medium">
                        Hapus
                      </button>
                    </div>
                  </div>
                  <div v-else>
                    <input type="file" accept="image/*" @change="handleThumbnail"
                      class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                  </div>
                </div>
                <TiptapEditor v-model="form.body_web" />
              </div>
            </template>

            <template #ig>
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Postingan</label>
                    <select v-model="form.ig_type"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                      <option value="feed">Feed</option>
                      <option value="reels">Reels</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Media Instagram</label>
                    <input type="file" accept="image/*,video/*" multiple @change="handleIGMedia"
                      class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    <div v-if="editableExistingInstagramMedia.length" class="mt-3 space-y-2">
                      <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">File Instagram Tersimpan</p>
                      <div v-for="media in editableExistingInstagramMedia" :key="media.id"
                        class="flex items-center gap-2 bg-gray-50 rounded p-2 border border-gray-200 transition">
                        <div class="text-xs font-semibold text-gray-400 w-5 text-center">#</div>
                        <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center bg-white border rounded overflow-hidden">
                          <img v-if="isImageMedia(media)" :src="fileUrl(media.path)" class="object-cover w-full h-full" />
                          <video v-else-if="isVideoMedia(media)" :src="fileUrl(media.path)" class="object-cover w-full h-full" controls />
                          <span v-else class="text-xs text-gray-400">File</span>
                        </div>
                        <div class="min-w-0 flex-1">
                          <p class="truncate text-xs font-medium text-gray-700">{{ fileName(media.path) }}</p>
                          <p class="text-[11px] text-gray-400">{{ media.type || 'media' }}</p>
                        </div>
                        <div class="flex gap-1 flex-shrink-0">
                          <a :href="fileUrl(media.path)" :download="fileName(media.path)"
                            class="text-blue-600 hover:text-blue-800 text-xs font-medium px-1.5 py-0.5 rounded hover:bg-blue-100">
                            Unduh
                          </a>
                          <label class="text-blue-500 hover:text-blue-700 text-xs font-medium px-1.5 py-0.5 rounded hover:bg-blue-100 cursor-pointer">
                            Ganti
                            <input type="file" accept="image/*,video/*" class="hidden" @change="e => replaceExistingIGMedia(media.id, e)" />
                          </label>
                          <button type="button" @click="removeExistingIGMedia(media.id)"
                            class="text-red-500 hover:text-red-700 text-xs font-medium px-1.5 py-0.5 rounded hover:bg-red-100">Hapus</button>
                        </div>
                      </div>
                    </div>
                    <div v-if="form.ig_media.length" class="mt-3 space-y-2">
                      <div v-for="(file, idx) in form.ig_media" :key="`${file.name}-${idx}`"
                        draggable="true" @dragstart="dragStart" @dragend="dragEnd" @dragover.prevent @drop="dropMedia(idx)"
                        class="flex items-center gap-2 bg-gray-50 rounded p-2 cursor-move hover:bg-blue-50 border border-transparent hover:border-blue-300 transition"
                        :data-index="idx">
                        <div class="text-xs font-semibold text-gray-400 w-5 text-center">{{ idx + 1 }}</div>
                        <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center bg-white border rounded overflow-hidden">
                          <img v-if="isImage(file)" :src="getFileUrl(file)" class="object-cover w-full h-full" />
                          <video v-else-if="isVideo(file)" :src="getFileUrl(file)" class="object-cover w-full h-full" controls />
                          <span v-else class="text-xs text-gray-400">File</span>
                        </div>
                        <span class="truncate flex-1 text-xs">{{ file.name }}</span>
                        <div class="flex gap-1">
                          <button v-if="idx > 0" type="button" @click="moveMediaUp(idx)"
                            class="text-blue-500 hover:text-blue-700 text-xs font-medium px-1.5 py-0.5 rounded hover:bg-blue-100" title="Naik">▲</button>
                          <button v-if="idx < form.ig_media.length - 1" type="button" @click="moveMediaDown(idx)"
                            class="text-blue-500 hover:text-blue-700 text-xs font-medium px-1.5 py-0.5 rounded hover:bg-blue-100" title="Turun">▼</button>
                          <button type="button" @click="removeIGMedia(idx)"
                            class="text-red-500 hover:text-red-700 text-xs font-medium px-1.5 py-0.5 rounded hover:bg-red-100">Hapus</button>
                          <label class="text-blue-500 hover:text-blue-700 text-xs font-medium px-1.5 py-0.5 rounded hover:bg-blue-100 cursor-pointer">
                            Ganti
                            <input type="file" accept="image/*,video/*" class="hidden" @change="e => replaceIGMedia(idx, e)" />
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
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
                  <IGPreviewCard :caption="form.caption_ig" :hashtags="form.hashtags_ig" :media="form.ig_media.length ? form.ig_media : editableExistingInstagramMedia" />
                </div>
              </div>
            </template>
          </ArticleFormTabs>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h2 class="font-semibold text-gray-900">File Pendukung</h2>
              <p class="text-xs text-gray-500 mt-1">Tambahkan dokumen, foto, atau video pendukung di bawah konten artikel.</p>
            </div>
            <label
              class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition cursor-pointer">
              + Tambah File
              <input type="file" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt"
                class="hidden" @change="handleSupportingFiles" />
            </label>
          </div>

          <div v-if="editableExistingSupportingFiles.length" class="space-y-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">File Pendukung Tersimpan</p>
            <div v-for="media in editableExistingSupportingFiles" :key="media.id"
              class="border border-gray-200 rounded-lg p-4 bg-gray-50">
              <div class="flex flex-col lg:flex-row gap-4">
                <div class="w-full lg:w-44 h-36 bg-white border rounded-lg overflow-hidden flex items-center justify-center">
                  <img v-if="isImageMedia(media)" :src="fileUrl(media.path)" class="object-cover w-full h-full" />
                  <video v-else-if="isVideoMedia(media)" :src="fileUrl(media.path)" class="object-cover w-full h-full" controls />
                  <div v-else class="text-center px-4">
                    <div class="text-3xl text-gray-300 mb-2">📄</div>
                    <p class="text-xs text-gray-500 break-all">{{ fileName(media.path) }}</p>
                  </div>
                </div>
                <div class="flex-1 space-y-3">
                  <div>
                    <p class="text-sm font-medium text-gray-900 break-all">{{ fileName(media.path) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ media.type || 'file' }}</p>
                    <p v-if="media.description" class="text-xs text-gray-500 mt-1 whitespace-pre-wrap">{{ media.description }}</p>
                  </div>
                  <div class="flex items-center gap-3 text-xs">
                    <a :href="fileUrl(media.path)" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                    <a :href="fileUrl(media.path)" :download="fileName(media.path)" class="text-blue-600 hover:underline">Unduh</a>
                    <button type="button" @click="removeExistingSupportingFile(media.id)" class="text-red-500 hover:underline">Hapus</button>
                    <label class="text-blue-600 hover:underline cursor-pointer">
                      Ubah File
                      <input type="file" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt" class="hidden" @change="e => replaceExistingSupportingFile(media.id, e)" />
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="supportingFiles.length" class="space-y-3">
            <div v-for="(file, idx) in supportingFiles" :key="`${file.name}-${idx}`"
              class="border border-gray-200 rounded-lg p-4 bg-gray-50">
              <div class="flex flex-col lg:flex-row gap-4">
                <div class="w-full lg:w-44 h-36 bg-white border rounded-lg overflow-hidden flex items-center justify-center">
                  <img v-if="isImage(file)" :src="getFileUrl(file)" class="object-cover w-full h-full" />
                  <video v-else-if="isVideo(file)" :src="getFileUrl(file)" class="object-cover w-full h-full" controls />
                  <div v-else class="text-center px-4">
                    <div class="text-3xl text-gray-300 mb-2">📄</div>
                    <p class="text-xs text-gray-500 break-all">{{ file.name }}</p>
                  </div>
                </div>
                <div class="flex-1 space-y-3">
                  <div>
                    <p class="text-sm font-medium text-gray-900 break-all">{{ file.name }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ Math.round(file.size / 1024) }} KB</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan File</label>
                    <input v-model="fileDescriptions[idx]" type="text"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                      placeholder="Contoh: Surat pendukung, foto kegiatan, video dokumentasi" />
                  </div>
                  <div class="flex items-center gap-3 text-xs">
                    <button type="button" @click="editSupportingFile(idx)" class="text-blue-600 hover:underline">Ubah File</button>
                    <button type="button" @click="removeSupportingFile(idx)" class="text-red-500 hover:underline">Hapus</button>
                  </div>
                </div>
              </div>
            </div>
            <input ref="editFileInput" type="file"
              accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt" class="hidden"
              @change="e => editingFileIdx !== null && replaceSupportingFile(editingFileIdx, e)" />
          </div>

          <div v-else class="border border-dashed border-gray-300 rounded-lg p-8 text-center text-sm text-gray-500">
            Belum ada file pendukung.
          </div>
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
import { ref, computed, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ArticleFormTabs from '@/Components/ArticleFormTabs.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';
import IGPreviewCard from '@/Components/IGPreviewCard.vue';
import { Link, useForm } from '@inertiajs/vue3';
import TagsInput from '@/Components/TagsInput.vue';

const props = defineProps({
  article: Object,
  categories: Array,
  isEditor: { type: Boolean, default: false },
  updateUrl: { type: String, default: '' },
});

const supportingFiles = ref([]);
const fileDescriptions = ref([]);
const editFileInput = ref(null);
let editingFileIdx = null;
const deletedInstagramMediaIds = ref([]);
const deletedSupportingFileIds = ref([]);
const thumbnailPreview = ref(null);

function extractTagText(tag) {
  if (!tag) return '';
  if (typeof tag === 'object') {
    const candidate = tag.text ?? tag.label ?? '';
    return typeof candidate === 'string' ? candidate.trim() : '';
  }

  const value = String(tag).trim();
  if (!value) return '';

  if ((value.startsWith('{') && value.endsWith('}')) || (value.startsWith('[') && value.endsWith(']'))) {
    try {
      const parsed = JSON.parse(value);
      if (parsed && typeof parsed === 'object' && typeof parsed.text === 'string') {
        return parsed.text.trim();
      }
    } catch (_) {
      // Keep raw string when not valid JSON.
    }
  }

  return value;
}

function normalizeSeoKeywords(value) {
  if (!value) return [];

  if (Array.isArray(value)) {
    return value
      .map((item) => extractTagText(item))
      .filter(Boolean);
  }

  if (typeof value === 'string') {
    return value
      .split(',')
      .map((item) => extractTagText(item))
      .filter(Boolean);
  }

  return [];
}

function handleSupportingFiles(e) {
  const files = Array.from(e.target.files);
  supportingFiles.value.push(...files);
  files.forEach(() => fileDescriptions.value.push(''));
}

function removeSupportingFile(idx) {
  supportingFiles.value.splice(idx, 1);
  fileDescriptions.value.splice(idx, 1);
}

function editSupportingFile(idx) {
  editingFileIdx = idx;
  nextTick(() => {
    if (editFileInput.value) editFileInput.value.click();
  });
}

function replaceSupportingFile(idx, e) {
  const files = e.target.files;
  if (files && files.length) {
    supportingFiles.value.splice(idx, 1, files[0]);
  }
  editingFileIdx = null;
}

const form = useForm({
  title: props.article.title ?? '',
  body_web: props.article.body_web ?? null,
  thumbnail: null,
  caption_ig: props.article.caption_ig ?? '',
  hashtags_ig: props.article.hashtags_ig ?? '',
  status: props.article.status ?? 'draft',
  category_id: props.article.category_id ?? '',
  published_at: (props.article.published_at || props.article.created_at || '').toString().slice(0, 10),
  ig_type: props.article.ig_type ?? 'feed',
  ig_media: [],
  ig_media_delete_ids: [],
  supporting_files: [],
  supporting_descriptions: [],
  target_platform: props.article.target_platform ?? 'web',
  excerpt: props.article.excerpt ?? '',
  seo: {
    seo_title: props.article.seo?.seo_title ?? '',
    seo_description: props.article.seo?.seo_description ?? '',
    seo_keywords: normalizeSeoKeywords(props.article.seo?.seo_keywords),
    og_title: props.article.seo?.og_title ?? '',
    og_description: props.article.seo?.og_description ?? '',
    og_image: props.article.seo?.og_image ?? '',
    canonical_url: props.article.seo?.canonical_url ?? '',
    robots_index: props.article.seo?.robots_index ?? true,
    robots_follow: props.article.seo?.robots_follow ?? true,
  },
});

const seoTitleLen = computed(() => form.seo.seo_title?.length ?? 0);
const seoDescLen = computed(() => form.seo.seo_description?.length ?? 0);
const bodyLen = computed(() => {
  if (!form.body_web || typeof form.body_web !== 'object') return 0;
  function countText(node) {
    if (!node) return 0;
    if (node.type === 'text' && node.text) return node.text.length;
    if (node.content && Array.isArray(node.content)) return node.content.reduce((sum, n) => sum + countText(n), 0);
    return 0;
  }
  return countText(form.body_web);
});

const seoChecks = computed(() => [
  { label: 'Judul diisi (min. 10 karakter)', pass: form.title && form.title.length >= 10, param: form.title?.length || 0 },
  { label: 'Kategori dipilih', pass: !!form.category_id, param: form.category_id },
  { label: 'Tanggal diisi', pass: !!form.published_at, param: form.published_at },
  { label: 'Thumbnail diunggah', pass: !!form.thumbnail || !!props.article.thumbnail, param: (form.thumbnail || props.article.thumbnail) ? 'Ada' : 'Kosong' },
  { label: 'Isi artikel min. 100 karakter', pass: bodyLen.value >= 100, param: bodyLen.value },
  { label: 'SEO title diisi (30-60 karakter)', pass: seoTitleLen.value >= 30 && seoTitleLen.value <= 60, param: seoTitleLen.value },
  { label: 'Meta description diisi (120-160 karakter)', pass: seoDescLen.value >= 120 && seoDescLen.value <= 160, param: seoDescLen.value },
  { label: 'Tags diisi', pass: Array.isArray(form.seo.seo_keywords) && form.seo.seo_keywords.length > 0, param: form.seo.seo_keywords?.length || 0 },
]);

const seoScore = computed(() => {
  const passing = seoChecks.value.filter(c => c.pass).length;
  return Math.round((passing / seoChecks.value.length) * 100);
});

const scoreColor = computed(() => {
  if (seoScore.value >= 80) return 'bg-green-500';
  if (seoScore.value >= 50) return 'bg-yellow-500';
  return 'bg-red-500';
});

const scoreTextColor = computed(() => {
  if (seoScore.value >= 80) return 'text-green-600';
  if (seoScore.value >= 50) return 'text-yellow-600';
  return 'text-red-600';
});

const seoScoreLabel = computed(() => {
  if (seoScore.value >= 80) return 'OK';
  if (seoScore.value >= 50) return 'Low';
  return 'Over';
});

const existingInstagramMedia = computed(() => {
  const allMedia = Array.isArray(props.article?.media) ? props.article.media : [];
  const tagged = allMedia.filter((m) => m?.alt_text === 'ig_media');
  if (tagged.length > 0) return tagged;

  return allMedia.filter((m) => {
    if (!m) return false;
    if (m.type === 'image' || m.type === 'video') return true;
    const source = `${m.path || m.url || ''}`.toLowerCase();
    return /\.(jpg|jpeg|png|gif|webp|mp4|mov|webm|ogg)$/i.test(source);
  });
});

const editableExistingInstagramMedia = computed(() => {
  return existingInstagramMedia.value.filter((media) => !deletedInstagramMediaIds.value.includes(media.id));
});

const existingSupportingFiles = computed(() => {
  const allMedia = Array.isArray(props.article?.media) ? props.article.media : [];
  const tagged = allMedia.filter((m) => m?.alt_text !== 'ig_media');
  return tagged;
});

const editableExistingSupportingFiles = computed(() => {
  return existingSupportingFiles.value.filter((media) => !deletedSupportingFileIds.value.includes(media.id));
});

function removeExistingIGMedia(mediaId) {
  if (!deletedInstagramMediaIds.value.includes(mediaId)) {
    deletedInstagramMediaIds.value.push(mediaId);
  }
}

function replaceExistingIGMedia(mediaId, e) {
  const files = e.target.files;
  if (files && files.length) {
    removeExistingIGMedia(mediaId);
    form.ig_media.push(files[0]);
  }
}

function removeExistingSupportingFile(mediaId) {
  if (!deletedSupportingFileIds.value.includes(mediaId)) {
    deletedSupportingFileIds.value.push(mediaId);
  }
}

function replaceExistingSupportingFile(mediaId, e) {
  const files = e.target.files;
  if (files && files.length) {
    const currentMedia = existingSupportingFiles.value.find((item) => item.id === mediaId);
    removeExistingSupportingFile(mediaId);
    form.supporting_files.push(files[0]);
    fileDescriptions.value.push(currentMedia?.description ?? '');
  }
}

function handleThumbnail(e) {
  const file = e.target.files[0];
  if (file) {
    form.thumbnail = file;
    thumbnailPreview.value = URL.createObjectURL(file);
  }
}

function deleteThumbnail() {
  form.thumbnail = null;
  if (thumbnailPreview.value) {
    URL.revokeObjectURL(thumbnailPreview.value);
  }
  thumbnailPreview.value = null;
}

function handleIGMedia(e) {
  const files = Array.from(e.target.files);
  if (form.ig_media.some(f => isVideo(f))) {
    const imageFiles = files.filter(f => isImage(f));
    form.ig_media.push(...imageFiles);
  } else {
    form.ig_media.push(...files);
  }
}

function removeIGMedia(idx) {
  form.ig_media.splice(idx, 1);
}

function replaceIGMedia(idx, e) {
  const files = e.target.files;
  if (files && files.length) {
    form.ig_media.splice(idx, 1, files[0]);
  }
}

let draggedIndex = null;

function dragStart(e) {
  draggedIndex = parseInt(e.currentTarget.dataset.index);
  e.dataTransfer.effectAllowed = 'move';
}

function dragEnd() {
  draggedIndex = null;
}

function dropMedia(targetIdx) {
  if (draggedIndex === null || draggedIndex === targetIdx) return;
  const item = form.ig_media[draggedIndex];
  form.ig_media.splice(draggedIndex, 1);
  form.ig_media.splice(targetIdx, 0, item);
  draggedIndex = null;
}

function moveMediaUp(idx) {
  if (idx > 0) {
    const item = form.ig_media[idx];
    form.ig_media.splice(idx, 1);
    form.ig_media.splice(idx - 1, 0, item);
  }
}

function moveMediaDown(idx) {
  if (idx < form.ig_media.length - 1) {
    const item = form.ig_media[idx];
    form.ig_media.splice(idx, 1);
    form.ig_media.splice(idx + 1, 0, item);
  }
}

async function save(status) {
  form.status = status;

  if (Array.isArray(form.seo.seo_keywords) && form.seo.seo_keywords.length > 0) {
    form.seo.seo_keywords = form.seo.seo_keywords
      .map((tag) => extractTagText(tag))
      .filter(Boolean)
      .join(',');
  } else {
    form.seo.seo_keywords = '';
  }

  form.supporting_files = supportingFiles.value;
  form.supporting_descriptions = fileDescriptions.value;
  form.ig_media_delete_ids = deletedInstagramMediaIds.value;
  form.supporting_file_delete_ids = deletedSupportingFileIds.value;

  await form.put(props.updateUrl || `/articles/${props.article.id}`);
}

function isImage(file) {
  return file && file.type && file.type.startsWith('image/');
}

function isVideo(file) {
  return file && file.type && file.type.startsWith('video/');
}

function getFileUrl(file) {
  return file ? URL.createObjectURL(file) : '';
}

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

function isImageMedia(media) {
  if (!media) return false;
  if (media.type === 'image') return true;
  return /\.(jpg|jpeg|png|gif|webp)$/i.test(`${media.path || ''}`);
}

function isVideoMedia(media) {
  if (!media) return false;
  if (media.type === 'video') return true;
  return /\.(mp4|mov|webm|ogg)$/i.test(`${media.path || ''}`);
}
</script>
