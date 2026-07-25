<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    title: string
    body: string
    slug?: string
}>()

function slugify(text: string) {
    return text
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const wordCount = computed(() => {
    const text = props.body.replace(/<[^>]+>/g, '')
    return text.split(/\s+/).filter(Boolean).length
})

const charCount = computed(() => {
    const text = props.body.replace(/<[^>]+>/g, '')
    return text.length
})

const headingCount = computed(() => {
    const h1 = (props.body.match(/<h1/g) || []).length
    const h2 = (props.body.match(/<h2/g) || []).length
    const h3 = (props.body.match(/<h3/g) || []).length
    return { h1, h2, h3 }
})

const images = computed(() => (props.body.match(/<img/g) || []).length)

const links = computed(() => (props.body.match(/<a /g) || []).length)

const titleLength = computed(() => props.title.length)

const slugMatch = computed(() => {
    if (!props.slug) return null
    return slugify(props.title) === props.slug
})

const checks = computed(() => {
    const list = [
        {
            label: 'Title length',
            ok: titleLength.value >= 30 && titleLength.value <= 60,
            hint: titleLength.value < 30 ? 'Title is too short (min 30 chars)' : titleLength.value > 60 ? 'Title is too long (max 60 chars)' : 'Good length for SEO',
        },
        {
            label: 'Word count',
            ok: wordCount.value >= 300,
            hint: wordCount.value < 300 ? 'Aim for at least 300 words' : 'Good content length',
        },
        {
            label: 'Heading structure',
            ok: headingCount.value.h1 <= 1 && headingCount.value.h2 > 0,
            hint: !headingCount.value.h2 ? 'Add H2 headings for structure' : headingCount.value.h1 > 1 ? 'Use only one H1' : 'Good heading structure',
        },
        {
            label: 'Images',
            ok: images.value >= 1,
            hint: !images.value ? 'Add at least one image' : `${images.value} image(s) found`,
        },
        {
            label: 'Links',
            ok: links.value >= 1,
            hint: !links.value ? 'Add internal/external links' : `${links.value} link(s) found`,
        },
        {
            label: 'Readability',
            ok: wordCount.value > 0,
            hint: 'Write clear, short sentences',
        },
    ]
    if (slugMatch.value !== null) {
        list.push({
            label: 'URL slug',
            ok: slugMatch.value,
            hint: slugMatch.value ? 'Slug matches title' : 'Slug should match the title (e.g. ' + slugify(props.title) + ')',
        })
    }
    return list
})

const score = computed(() => {
    const passed = checks.value.filter(c => c.ok).length
    return Math.round((passed / checks.value.length) * 100)
})

const scoreColor = computed(() => {
    if (score.value >= 80) return 'text-green-600'
    if (score.value >= 50) return 'text-yellow-600'
    return 'text-red-600'
})
</script>

<template>
    <div class="space-y-4 rounded-lg border p-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">SEO Analysis</h3>
            <span :class="['text-2xl font-bold', scoreColor]">{{ score }}%</span>
        </div>

        <div class="h-2 rounded-full bg-muted">
            <div
                :class="['h-2 rounded-full transition-all', score >= 80 ? 'bg-green-500' : score >= 50 ? 'bg-yellow-500' : 'bg-red-500']"
                :style="{ width: `${score}%` }"
            />
        </div>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between text-muted-foreground">
                <span>{{ wordCount }} words</span>
                <span>{{ charCount }} chars</span>
            </div>
            <div class="flex gap-2 text-muted-foreground">
                <span>H1:{{ headingCount.h1 }}</span>
                <span>H2:{{ headingCount.h2 }}</span>
                <span>H3:{{ headingCount.h3 }}</span>
                <span>{{ images }} img</span>
            </div>
        </div>

        <ul class="space-y-1.5 text-sm">
            <li
                v-for="check in checks"
                :key="check.label"
                class="flex items-start gap-2"
            >
                <span :class="check.ok ? 'text-green-600' : 'text-red-500'">
                    {{ check.ok ? '✓' : '✗' }}
                </span>
                <div>
                    <span :class="check.ok ? 'text-green-600' : 'text-red-500'">{{ check.label }}</span>
                    <p v-if="!check.ok" class="text-xs text-muted-foreground">{{ check.hint }}</p>
                    <p v-else class="text-xs text-muted-foreground">{{ check.hint }}</p>
                </div>
            </li>
        </ul>

        <div class="text-xs text-muted-foreground">
            <p>Add meta description, alt text to images, and use descriptive URLs for best SEO.</p>
        </div>
    </div>
</template>
