<template>
  <div class="seo-agent">
    <div v-if="validation.title">
      <span :class="{'text-red-500': !validation.title.valid}">{{ validation.title.message }}</span>
      <div v-if="suggestions.title">
        <span class="text-blue-500">AI Suggestion: {{ suggestions.title }}</span>
      </div>
    </div>
    <div v-if="validation.category">
      <span :class="{'text-red-500': !validation.category.valid}">{{ validation.category.message }}</span>
    </div>
    <div v-if="validation.thumbnail">
      <span :class="{'text-red-500': !validation.thumbnail.valid}">{{ validation.thumbnail.message }}</span>
    </div>
    <div v-if="validation.wordCount">
      <span :class="{'text-red-500': !validation.wordCount.valid}">{{ validation.wordCount.message }}</span>
    </div>
    <div v-if="validation.metaDescription">
      <span :class="{'text-red-500': !validation.metaDescription.valid}">{{ validation.metaDescription.message }}</span>
      <div v-if="suggestions.metaDescription">
        <span class="text-blue-500">AI Suggestion: {{ suggestions.metaDescription }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  article: Object
});

const validation = ref({
  title: null,
  category: null,
  thumbnail: null,
  wordCount: null,
  metaDescription: null
});

const suggestions = ref({
  title: null,
  metaDescription: null
});

watch(() => props.article, async (newArticle) => {
  validate(newArticle);
  await getAISuggestions(newArticle);
}, { deep: true, immediate: true });

function validate(article) {
  // Title
  if (!article.title || article.title.length < 10) {
    validation.value.title = { valid: false, message: 'Title is too short.' };
  } else if (article.title.length > 60) {
    validation.value.title = { valid: false, message: 'Title is too long.' };
  } else {
    validation.value.title = { valid: true, message: 'Title looks good.' };
  }
  // Category
  validation.value.category = article.category_id ? { valid: true, message: 'Category selected.' } : { valid: false, message: 'Category is required.' };
  // Thumbnail
  validation.value.thumbnail = article.thumbnail ? { valid: true, message: 'Thumbnail set.' } : { valid: false, message: 'Thumbnail is missing.' };
  // Word count
  const wordCount = (article.body_web && typeof article.body_web === 'string') ? article.body_web.split(/\s+/).length : 0;
  if (wordCount < 300) {
    validation.value.wordCount = { valid: false, message: 'Article is too short.' };
  } else {
    validation.value.wordCount = { valid: true, message: `Word count: ${wordCount}` };
  }
  // Meta description
  if (!article.seo_description || article.seo_description.length < 50) {
    validation.value.metaDescription = { valid: false, message: 'Meta description is too short.' };
  } else if (article.seo_description.length > 160) {
    validation.value.metaDescription = { valid: false, message: 'Meta description is too long.' };
  } else {
    validation.value.metaDescription = { valid: true, message: 'Meta description looks good.' };
  }
}

async function getAISuggestions(article) {
  try {
    const { data } = await axios.post('/api/seo-ai-suggest', {
      title: article.title,
      metaDescription: article.seo_description
    });
    suggestions.value.title = data.title_suggestion;
    suggestions.value.metaDescription = data.meta_description_suggestion;
  } catch (e) {
    suggestions.value.title = null;
    suggestions.value.metaDescription = null;
  }
}
</script>

<style scoped>
.seo-agent {
  margin-top: 1rem;
}
</style>
