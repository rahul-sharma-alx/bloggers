<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import SeoPanel from '@/components/SeoPanel.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit, index, update } from '@/routes/posts';
import type { Post, Team } from '@/types';

type Props = { post: Post };

const props = defineProps<Props>();

defineOptions({
    layout: (props: { post: Post; currentTeam?: Team | null }) => ({
        breadcrumbs: [
            { title: 'Posts', href: props.currentTeam ? index(props.currentTeam.slug) : '/' },
            { title: props.post.title, href: props.currentTeam ? edit([props.currentTeam.slug, props.post.id]) : '/' },
        ],
    }),
});

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team | undefined);
const body = ref(props.post.body);
const title = ref(props.post.title);
const slug = ref(props.post.slug);
const slugManuallyEdited = ref(false);
const featuredImage = ref(props.post.featured_image ?? '');

function slugify(text: string) {
    return text
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

watch(title, (val) => {
    if (!slugManuallyEdited.value) {
        slug.value = slugify(val)
    }
})

const previewUrl = computed(() => {
    if (featuredImage.value) return featuredImage.value;
    const match = body.value.match(/<img[^>]+src="([^"]+)"/);
    return match ? match[1] : null;
});
</script>

<template>
    <Head :title="`Edit: ${post.title}`" />
    <h1 class="sr-only">Edit Post</h1>

    <div class="flex flex-col space-y-6">
        <Heading variant="small" title="Edit Post" :description="`Editing: ${post.title}`" />

        <Form
            v-if="currentTeam"
            v-bind="update.form([currentTeam.slug, post.id])"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input id="title" class="mt-1 block w-full" name="title" :default-value="post.title" required placeholder="Post title" @input="title = ($event.target as HTMLInputElement).value" />
                        <InputError class="mt-2" :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="slug">Slug</Label>
                        <Input
                            id="slug"
                            class="mt-1 block w-full font-mono text-sm"
                            name="slug"
                            placeholder="auto-generated-from-title"
                            :default-value="slug"
                            @input="slug = ($event.target as HTMLInputElement).value; slugManuallyEdited = true"
                        />
                        <InputError class="mt-2" :message="errors.slug" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="body">Body</Label>
                        <RichTextEditor v-model="body" />
                        <input type="hidden" name="body" :value="body" />
                        <InputError class="mt-2" :message="errors.body" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Featured Image</Label>
                        <div class="flex items-start gap-4">
                            <div class="flex-1">
                                <Input
                                    :default-value="featuredImage"
                                    placeholder="Featured image: https://unsplash.com/photos"
                                    @input="featuredImage = ($event.target as HTMLInputElement).value"
                                />
                            </div>
                            <div v-if="previewUrl" class="h-24 w-40 flex-shrink-0 overflow-hidden rounded-lg border">
                                <img :src="previewUrl" alt="Featured image preview" class="h-full w-full object-cover" />
                            </div>
                        </div>
                        <input type="hidden" name="featured_image" :value="featuredImage" />
                    </div>
                </div>

                <div class="space-y-6">
                    <SeoPanel :title="title" :body="body" :slug="slug" />

                    <div class="rounded-lg border p-4">
                        <h3 class="mb-2 font-semibold">Preview</h3>
                        <div v-if="previewUrl" class="mb-3">
                            <img :src="previewUrl" alt="Featured image" class="w-full rounded-lg object-cover" />
                        </div>
                        <h4 class="text-lg font-bold">{{ post.title }}</h4>
                        <div class="mt-2 text-sm text-muted-foreground line-clamp-4" v-html="body" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" class="w-full">Update</Button>
                    </div>
                </div>
            </div>
        </Form>
    </div>
</template>
