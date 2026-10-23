<script setup lang="ts">
/**
 * Renders a markdown body as prose. `html` is made on the server by
 * App\Support\Markdown::html(), which escapes any HTML typed into the
 * markdown; it is the only thing allowed in `v-html`.
 */
defineProps<{ html?: string | null }>();
</script>

<template>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div
        class="prose mt-10 max-w-none prose-stone dark:prose-invert"
        v-html="html"
    />
</template>
