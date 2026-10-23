<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ContentBody from '@/components/site/ContentBody.vue';
import { formatLongDate, pluralize } from '@/lib/format';
import type { ArticleDetail } from '@/types/content';

defineProps<{ article: ArticleDetail }>();
</script>

<template>
    <article v-if="article" class="mx-auto w-full max-w-4xl">
        <header class="border-b border-stone-200 pb-8 dark:border-stone-800">
            <Link
                href="/articles"
                class="text-sm font-bold text-teal-700 dark:text-teal-300"
            >
                ← All articles
            </Link>
            <h1 class="mt-6 text-5xl font-black tracking-tight sm:text-6xl">
                {{ article.title }}
            </h1>
            <p
                class="mt-5 max-w-2xl text-xl leading-8 text-stone-600 dark:text-stone-300"
            >
                {{ article.description }}
            </p>
            <div
                class="mt-5 flex flex-wrap gap-3 text-sm text-stone-500 dark:text-stone-400"
            >
                <span v-if="article.publishedAt"
                    >Published {{ formatLongDate(article.publishedAt) }}</span
                >
                <span
                    >{{
                        pluralize(article.readingTimeMinutes, 'minute')
                    }}
                    read</span
                >
            </div>
            <img
                v-if="article.heroImageUrl"
                :src="article.heroImageUrl"
                :alt="article.heroImageAlt || article.title"
                class="mt-8 aspect-[16/7] w-full rounded-3xl object-cover"
            />
        </header>

        <ContentBody :html="article.bodyHtml" />
    </article>
</template>
