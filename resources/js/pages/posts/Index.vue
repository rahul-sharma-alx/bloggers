<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { create, destroy, edit, index } from '@/routes/posts';
import type { Post, Team } from '@/types';

type Props = {
    posts: Post[];
};

defineProps<Props>();

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Posts',
                href: props.currentTeam
                    ? index(props.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team | undefined);
</script>

<template>
    <Head title="Posts" />

    <h1 class="sr-only">Posts</h1>

    <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                title="Posts"
                description="Your blog posts"
            />

            <Link
                v-if="currentTeam"
                :href="create(currentTeam.slug)"
            >
                <Button data-test="posts-new-button">
                    <Plus /> New post
                </Button>
            </Link>
        </div>

        <div class="space-y-3">
            <div
                v-for="post in posts"
                :key="post.id"
                data-test="post-row"
                class="flex items-center justify-between gap-4 rounded-lg border p-4"
            >
                <div>
                    <div class="font-medium">{{ post.title }}</div>
                    <div class="text-sm text-muted-foreground">
                        {{ post.slug }}
                    </div>
                </div>

                <TooltipProvider>
                    <div
                        v-if="currentTeam"
                        class="flex items-center gap-2"
                    >
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Link
                                    :href="edit([currentTeam.slug, post.id])"
                                >
                                    <Button
                                        data-test="post-view-button"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                </Link>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>View post</p>
                            </TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Link
                                    :href="edit([currentTeam.slug, post.id])"
                                >
                                    <Button
                                        data-test="post-edit-button"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                </Link>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Edit post</p>
                            </TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Link
                                    :href="destroy([currentTeam.slug, post.id])"
                                    as="button"
                                >
                                    <Button
                                        data-test="post-delete-button"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </Link>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Delete post</p>
                            </TooltipContent>
                        </Tooltip>
                    </div>
                </TooltipProvider>
            </div>

            <p
                v-if="posts.length === 0"
                class="py-8 text-center text-muted-foreground"
            >
                No posts yet.
            </p>
        </div>
    </div>
</template>
