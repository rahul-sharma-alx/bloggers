<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Heart, MessageSquare, Share2 } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { Post } from '@/types';

type Props = {
    post: Post;
    related: Post[];
    liked: boolean;
    likesCount: number;
};

const props = defineProps<Props>();

defineOptions({ layout: null });
const page = usePage();

const isAuthenticated = computed(() => !!page.props.auth?.user);
const commentCount = computed(() => props.post.comments?.length ?? 0);

const heroImage = computed(() => {
    if (props.post.featured_image) return props.post.featured_image
    const match = props.post.body?.match(/<img[^>]+src="([^"]+)"/)
    return match?.[1] ?? null
})

function toggleLike() {
    if (!isAuthenticated.value) return;
    router.post(`/articles/${props.post.slug}/like`);
}

function submitComment(e: Event) {
    const form = e.target as HTMLFormElement;
    const body = new FormData(form).get('body') as string;
    if (!body?.trim()) return;
    router.post(`/articles/${props.post.slug}/comment`, { body });
    form.reset();
}
</script>

<template>
    <Head :title="post.title" />

    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_300px]">
            <!-- Main content -->
            <article>
                <div v-if="heroImage" class="mb-6 overflow-hidden rounded-xl">
                    <img :src="heroImage" alt="" class="w-full object-cover" />
                </div>

                <h1 class="mb-4 text-3xl font-bold">{{ post.title }}</h1>

                <div class="mb-6 flex items-center gap-4 text-sm text-muted-foreground">
                    <span>{{ post.user?.name ?? 'Unknown' }}</span>
                    <span>·</span>
                    <span>{{ new Date(post.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                </div>

                <div class="prose prose-lg max-w-none" v-html="post.body" />

                <div class="mt-8 flex items-center gap-4 border-t pt-4">
                    <Button variant="ghost" size="sm" :class="{ 'text-red-500': liked }" @click="toggleLike">
                        <Heart :class="liked ? 'fill-current' : ''" class="h-5 w-5" />
                        <span class="ml-1">{{ likesCount }}</span>
                    </Button>
                    <Button variant="ghost" size="sm">
                        <MessageSquare class="h-5 w-5" />
                        <span class="ml-1">{{ commentCount }}</span>
                    </Button>
                    <Button variant="ghost" size="sm" @click="navigator.clipboard?.writeText(window.location.href)">
                        <Share2 class="h-5 w-5" />
                    </Button>
                </div>

                <!-- Comments -->
                <div class="mt-8 border-t pt-6">
                    <h2 class="mb-4 text-xl font-semibold">Comments ({{ commentCount }})</h2>

                    <form v-if="isAuthenticated" class="mb-6 space-y-3" @submit.prevent="submitComment">
                        <textarea
                            name="body"
                            rows="3"
                            class="w-full rounded-lg border border-input bg-background p-3 text-sm text-foreground outline-none focus:ring-2 focus:ring-ring"
                            placeholder="Write a comment..."
                            required
                        />
                        <Button type="submit" size="sm">Post Comment</Button>
                    </form>
                    <p v-else class="mb-6 text-sm text-muted-foreground">
                        <Link href="/login" class="underline">Sign in</Link> to leave a comment.
                    </p>

                    <div v-if="post.comments?.length" class="space-y-4">
                        <div v-for="comment in post.comments" :key="comment.id" class="rounded-lg border p-4">
                            <div class="mb-1 flex items-center gap-2 text-sm">
                                <span class="font-medium">{{ comment.user?.name }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{ new Date(comment.created_at).toLocaleDateString() }}
                                </span>
                            </div>
                            <p class="text-sm">{{ comment.body }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">No comments yet.</p>
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="space-y-6">
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold">Related Articles</h3>
                    <div v-if="related.length" class="space-y-3">
                        <Link
                            v-for="r in related"
                            :key="r.id"
                            :href="`/articles/${r.slug}`"
                            class="group block"
                        >
                            <div class="flex gap-3">
                                <div v-if="r.featured_image" class="h-14 w-20 flex-shrink-0 overflow-hidden rounded">
                                    <img :src="r.featured_image" alt="" class="h-full w-full object-cover" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="line-clamp-2 text-sm font-medium group-hover:underline">{{ r.title }}</p>
                                    <p class="text-xs text-muted-foreground">{{ new Date(r.created_at).toLocaleDateString() }}</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">No related articles.</p>
                </div>
            </aside>
        </div>
    </div>
</template>
