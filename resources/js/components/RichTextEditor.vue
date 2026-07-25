<script setup lang="ts">
import { watch, onBeforeUnmount, ref } from 'vue'
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import UnderlineExt from '@tiptap/extension-underline'
import LinkExt from '@tiptap/extension-link'
import ImageExt from '@tiptap/extension-image'
import TextAlignExt from '@tiptap/extension-text-align'
import PlaceholderExt from '@tiptap/extension-placeholder'
import {
    AlignCenter,
    AlignLeft,
    AlignRight,
    Bold,
    Code,
    FileCode,
    Heading1,
    Heading2,
    Heading3,
    Image,
    Italic,
    Link,
    List,
    ListOrdered,
    Minus,
    Quote,
    Redo2,
    Strikethrough,
    Underline as UnderlineIcon,
    Undo2,
    ImagePlus,
    ImageOff,
} from '@lucide/vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const mode = ref<'visual' | 'html'>('visual')
const htmlContent = ref('')
const showImageOptions = ref(false)

const editor = new Editor({
    extensions: [
        StarterKit.configure({ heading: { levels: [1, 2, 3] } }),
        UnderlineExt,
        LinkExt.configure({ openOnClick: false }),
        ImageExt,
        TextAlignExt.configure({ types: ['heading', 'paragraph'] }),
        PlaceholderExt.configure({ placeholder: 'Write your post content here...' }),
    ],
    content: props.modelValue,
    onCreate: () => editor.commands.focus(),
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    },
})

watch(() => props.modelValue, (value) => {
    if (mode.value === 'visual' && value !== editor.getHTML()) {
        const { from, to } = editor.state.selection
        editor.commands.setContent(value, false)
        if (from && to) editor.commands.setTextSelection({ from, to })
    }
})

onBeforeUnmount(() => { editor.destroy() })

function toggleMode() {
    if (mode.value === 'visual') {
        htmlContent.value = editor.getHTML()
        mode.value = 'html'
    } else {
        editor.commands.setContent(htmlContent.value, false)
        mode.value = 'visual'
        emit('update:modelValue', htmlContent.value)
    }
}

function addLink() {
    const url = prompt('Enter URL')
    if (url) editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

function addImageByUrl() {
    const url = prompt('Enter image URL')
    if (url) editor.chain().focus().setImage({ src: url }).run()
}

function setImageFloat(align: 'left' | 'center' | 'right') {
    const el = editor.view.dom.querySelector('.ProseMirror img[style*="float"]') as HTMLElement
        ?? editor.view.dom.querySelector('.ProseMirror img') as HTMLElement
    if (!el) return
    el.style.float = align === 'center' ? 'none' : align
    el.style.display = align === 'center' ? 'block' : ''
    el.style.margin = align === 'center' ? '0 auto' : '0.5em 0'
    if (align === 'left') el.style.marginRight = '1em'
    if (align === 'right') el.style.marginLeft = '1em'
    editor.commands.focus()
}

function removeImage() {
    editor.chain().focus().deleteSelection().run()
}
</script>

<template>
    <div class="rte-wrapper">
        <div class="rte-toolbar">
            <button type="button" class="rte-btn" @click="editor.chain().focus().undo().run()" title="Undo"><Undo2 class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" @click="editor.chain().focus().redo().run()" title="Redo"><Redo2 class="h-4 w-4" /></button>

            <span class="rte-sep" />

            <button type="button" class="rte-btn" :class="{ active: editor.isActive('bold') }" @click="editor.chain().focus().toggleBold().run()" title="Bold (Ctrl+B)"><Bold class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive('italic') }" @click="editor.chain().focus().toggleItalic().run()" title="Italic"><Italic class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive('underline') }" @click="editor.chain().focus().toggleUnderline().run()" title="Underline"><UnderlineIcon class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive('strike') }" @click="editor.chain().focus().toggleStrike().run()" title="Strikethrough"><Strikethrough class="h-4 w-4" /></button>

            <span class="rte-sep" />

            <button type="button" class="rte-btn" :class="{ active: editor.isActive('heading', { level: 1 }) }" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" title="Heading 1"><Heading1 class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive('heading', { level: 2 }) }" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" title="Heading 2"><Heading2 class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive('heading', { level: 3 }) }" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" title="Heading 3"><Heading3 class="h-4 w-4" /></button>

            <span class="rte-sep" />

            <button type="button" class="rte-btn" :class="{ active: editor.isActive('bulletList') }" @click="editor.chain().focus().toggleBulletList().run()" title="Bullet list"><List class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive('orderedList') }" @click="editor.chain().focus().toggleOrderedList().run()" title="Ordered list"><ListOrdered class="h-4 w-4" /></button>

            <span class="rte-sep" />

            <button type="button" class="rte-btn" :class="{ active: editor.isActive('blockquote') }" @click="editor.chain().focus().toggleBlockquote().run()" title="Blockquote"><Quote class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive('codeBlock') }" @click="editor.chain().focus().toggleCodeBlock().run()" title="Code block"><Code class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" @click="editor.chain().focus().setHorizontalRule().run()" title="Horizontal rule"><Minus class="h-4 w-4" /></button>

            <span class="rte-sep" />

            <button type="button" class="rte-btn" :class="{ active: editor.isActive('link') }" @click="addLink" title="Link"><Link class="h-4 w-4" /></button>
            <div class="relative inline-flex">
                <button type="button" class="rte-btn" @click="showImageOptions = !showImageOptions" title="Image"><Image class="h-4 w-4" /></button>
                <div v-if="showImageOptions" class="absolute left-0 top-full z-50 mt-1 w-48 rounded-lg border bg-popover p-2 shadow-md">
                    <button type="button" class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-accent" @click="showImageOptions = false; addImageByUrl()">
                        <ImagePlus class="h-4 w-4" /> Add by URL
                    </button>
                    <p class="px-2 py-1 text-xs text-muted-foreground">Image layout:</p>
                    <div class="flex gap-1 px-1">
                        <button type="button" class="rte-btn rte-btn-sm" @click="setImageFloat('left')" title="Float left"><AlignLeft class="h-3 w-3" /></button>
                        <button type="button" class="rte-btn rte-btn-sm" @click="setImageFloat('center')" title="Center"><AlignCenter class="h-3 w-3" /></button>
                        <button type="button" class="rte-btn rte-btn-sm" @click="setImageFloat('right')" title="Float right"><AlignRight class="h-3 w-3" /></button>
                        <button type="button" class="rte-btn rte-btn-sm" @click="removeImage" title="Remove selected image"><ImageOff class="h-3 w-3" /></button>
                    </div>
                </div>
            </div>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive({ textAlign: 'start' }) }" @click="editor.chain().focus().setTextAlign('start').run()" title="Align left"><AlignLeft class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive({ textAlign: 'center' }) }" @click="editor.chain().focus().setTextAlign('center').run()" title="Align center"><AlignCenter class="h-4 w-4" /></button>
            <button type="button" class="rte-btn" :class="{ active: editor.isActive({ textAlign: 'end' }) }" @click="editor.chain().focus().setTextAlign('end').run()" title="Align right"><AlignRight class="h-4 w-4" /></button>

            <span class="rte-sep rte-sep-spacer" />

            <button type="button" class="rte-btn" :class="{ active: mode === 'html' }" @click="toggleMode" title="Toggle HTML source"><FileCode class="h-4 w-4" /></button>
        </div>

        <textarea v-if="mode === 'html'" v-model="htmlContent" class="rte-html" rows="20" @input="emit('update:modelValue', htmlContent)" />

        <EditorContent v-else :editor="editor" class="rte-content" />
    </div>
</template>

<style>
.rte-wrapper { overflow: hidden; border-radius: 0.5rem; border: 1px solid var(--border); background: var(--background); }
.rte-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.125rem; border-bottom: 1px solid var(--border); background: var(--muted); padding: 0.25rem; }
.rte-btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 0.25rem; padding: 0.375rem; color: var(--muted-foreground); transition: all 0.15s; cursor: pointer; border: none; background: transparent; }
.rte-btn:hover { background: var(--accent); color: var(--accent-foreground); }
.rte-btn.active { background: var(--accent); color: var(--accent-foreground); }
.rte-btn-sm { padding: 0.25rem; }
.rte-sep { display: inline-block; width: 1px; height: 1.25rem; background: var(--border); margin: 0 0.25rem; }
.rte-sep-spacer { flex: 1; background: transparent; }
.rte-html { width: 100%; resize: vertical; border: 0; background: var(--background); padding: 1rem; font-family: ui-monospace, monospace; font-size: 0.875rem; color: var(--foreground); outline: none; min-height: 400px; }
.rte-html:focus { outline: none; }
.rte-content .ProseMirror { min-height: 400px; outline: none; padding: 1rem; color: var(--foreground); line-height: 1.75; }
.rte-content .ProseMirror p { margin: 0.5em 0; color: var(--foreground); }
.rte-content .ProseMirror h1 { font-size: 1.875rem; font-weight: 700; line-height: 1.2; margin: 0.75em 0 0.5em; color: var(--foreground); }
.rte-content .ProseMirror h2 { font-size: 1.5rem; font-weight: 600; line-height: 1.3; margin: 0.75em 0 0.5em; color: var(--foreground); }
.rte-content .ProseMirror h3 { font-size: 1.25rem; font-weight: 600; line-height: 1.4; margin: 0.75em 0 0.5em; color: var(--foreground); }
.rte-content .ProseMirror strong { font-weight: 700; }
.rte-content .ProseMirror em { font-style: italic; }
.rte-content .ProseMirror u { text-decoration: underline; }
.rte-content .ProseMirror s { text-decoration: line-through; }
.rte-content .ProseMirror ul { list-style-type: disc; padding-left: 1.5em; margin: 0.5em 0; }
.rte-content .ProseMirror ol { list-style-type: decimal; padding-left: 1.5em; margin: 0.5em 0; }
.rte-content .ProseMirror li { margin: 0.25em 0; }
.rte-content .ProseMirror blockquote { border-left: 3px solid var(--border); margin: 0.75em 0; padding: 0.5em 1em; color: var(--muted-foreground); font-style: italic; }
.rte-content .ProseMirror pre { background: var(--muted); border-radius: 0.375rem; margin: 0.75em 0; padding: 0.75em 1em; font-family: ui-monospace, monospace; font-size: 0.875rem; overflow-x: auto; }
.rte-content .ProseMirror code { background: var(--muted); border-radius: 0.25rem; font-size: 0.875em; padding: 0.125em 0.25em; }
.rte-content .ProseMirror pre code { background: none; padding: 0; }
.rte-content .ProseMirror img { max-width: 100%; height: auto; border-radius: 0.375rem; margin: 1em 0; display: block; }
.rte-content .ProseMirror img[style*="float: left"] { margin: 0.5em 1em 0.5em 0; max-width: 50%; }
.rte-content .ProseMirror img[style*="float: right"] { margin: 0.5em 0 0.5em 1em; max-width: 50%; }
.rte-content .ProseMirror hr { border: none; border-top: 1px solid var(--border); margin: 1.5em 0; }
.rte-content .ProseMirror a { color: var(--ring); text-decoration: underline; cursor: pointer; }
.rte-content .ProseMirror p.is-editor-empty:first-child::before { color: var(--muted-foreground); content: attr(data-placeholder); float: left; height: 0; pointer-events: none; }
</style>
