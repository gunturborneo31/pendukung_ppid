<template>
  <div class="tiptap-wrapper border border-gray-200 rounded-lg overflow-hidden">
    <!-- Toolbar -->
    <div class="toolbar flex flex-wrap gap-1 p-2 border-b bg-gray-50">
      <button type="button" @click="editor?.chain().focus().toggleBold().run()"
        :class="{ active: editor?.isActive('bold') }" title="Bold">
        <strong>B</strong>
      </button>
      <button type="button" @click="editor?.chain().focus().toggleItalic().run()"
        :class="{ active: editor?.isActive('italic') }" title="Italic">
        <em>I</em>
      </button>
      <button type="button" @click="editor?.chain().focus().toggleStrike().run()"
        :class="{ active: editor?.isActive('strike') }" title="Strikethrough">
        <s>S</s>
      </button>
      <div class="w-px bg-gray-300 mx-1"></div>
      <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
        :class="{ active: editor?.isActive('heading', { level: 1 }) }">H1</button>
      <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
        :class="{ active: editor?.isActive('heading', { level: 2 }) }">H2</button>
      <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
        :class="{ active: editor?.isActive('heading', { level: 3 }) }">H3</button>
      <div class="w-px bg-gray-300 mx-1"></div>
      <button type="button" @click="editor?.chain().focus().toggleBulletList().run()"
        :class="{ active: editor?.isActive('bulletList') }">• List</button>
      <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()"
        :class="{ active: editor?.isActive('orderedList') }">1. List</button>
      <div class="w-px bg-gray-300 mx-1"></div>
      <button type="button" @click="editor?.chain().focus().toggleBlockquote().run()"
        :class="{ active: editor?.isActive('blockquote') }">❝ Quote</button>
      <button type="button" @click="editor?.chain().focus().setHorizontalRule().run()">— HR</button>
      <div class="w-px bg-gray-300 mx-1"></div>
      <button type="button" @click="setLink" :class="{ active: editor?.isActive('link') }">🔗 Link</button>
      <button type="button" @click="addImage">🖼 Image</button>
      <div class="w-px bg-gray-300 mx-1"></div>
      <button type="button" @click="editor?.chain().focus().undo().run()" :disabled="!editor?.can().undo()">↩ Undo</button>
      <button type="button" @click="editor?.chain().focus().redo().run()" :disabled="!editor?.can().redo()">↪ Redo</button>
    </div>

    <!-- Editor Content -->
    <editor-content :editor="editor" class="prose max-w-none" />

    <!-- Word Count -->
    <div class="flex justify-between text-xs text-gray-400 px-3 py-1.5 bg-gray-50 border-t">
      <span>{{ wordCount }} kata</span>
      <span>{{ charCount }} karakter</span>
    </div>
  </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import { computed, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: { type: Object, default: null },
  placeholder: { type: String, default: 'Tulis konten artikel di sini...' },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Image.configure({ inline: false, allowBase64: false }),
    Link.configure({ openOnClick: false }),
    Placeholder.configure({ placeholder: props.placeholder }),
  ],
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getJSON());
  },
});

watch(() => props.modelValue, (val) => {
  if (editor.value && JSON.stringify(val) !== JSON.stringify(editor.value.getJSON())) {
    editor.value.commands.setContent(val, false);
  }
});

const wordCount = computed(() => {
  const text = editor.value?.getText() ?? '';
  return text.trim() ? text.trim().split(/\s+/).length : 0;
});

const charCount = computed(() => editor.value?.getText().length ?? 0);

function setLink() {
  const previousUrl = editor.value?.getAttributes('link').href;
  const url = window.prompt('URL Link:', previousUrl);
  if (url === null) return;
  if (url === '') {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }
  editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
}

function addImage() {
  const url = window.prompt('URL Gambar:');
  if (url) {
    editor.value?.chain().focus().setImage({ src: url }).run();
  }
}

onBeforeUnmount(() => {
  editor.value?.destroy();
});
</script>
