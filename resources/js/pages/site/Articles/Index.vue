<script setup lang="ts">
import ArticleCard from '@/components/site/ArticleCard.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import type { ArticleItem } from '@/types/content';

defineProps<{ articles: ArticleItem[] }>();
</script>

<template>
    <section>
        <PageHeader title="Articles" eyebrow="News from the hall" />

        <div
            v-if="articles?.length"
            class="mt-10 grid gap-8 md:grid-cols-2 xl:grid-cols-3"
        >
            <ArticleCard
                v-for="article in articles"
                :key="article.slug"
                :article="article"
            />
        </div>
        <p v-else class="mt-10 text-lg text-stone-600 dark:text-stone-300">
            No articles yet — check back soon.
        </p>
    </section>
</template>
