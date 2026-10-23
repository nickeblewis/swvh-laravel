<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ContactForm from '@/components/site/ContactForm.vue';
import ContactSent from '@/components/site/ContactSent.vue';
import ContentBody from '@/components/site/ContentBody.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import type { PageDetail } from '@/types/content';

/**
 * An admin-created page. The built-in pages have their own routes, which
 * come first, which is why their slugs are reserved (`site.reserved_slugs`).
 */
defineProps<{ page: PageDetail }>();

const sent = computed(() => usePage().props.enquirySent);
</script>

<template>
    <ContactSent v-if="sent" />
    <article v-else-if="page" class="mx-auto w-full max-w-4xl">
        <PageHeader
            :title="page.title"
            :intro="page.description"
            :image="page.heroImagePath"
            :image-alt="page.heroImageAlt"
        />
        <ContentBody :html="page.bodyHtml" />
        <div
            v-if="page.template === 'contact'"
            class="mt-12 border-t border-stone-200 pt-10 dark:border-stone-800"
        >
            <ContactForm />
        </div>
    </article>
</template>
