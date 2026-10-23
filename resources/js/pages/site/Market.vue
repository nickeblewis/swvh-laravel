<script setup lang="ts">
import PageHeader from '@/components/site/PageHeader.vue';
import { useSite } from '@/composables/useSite';
import type { Occurrence } from '@/types/content';

// The badge under the title comes from the market's entry in Regular Activities.
const data = defineProps<{ occurrence: Occurrence | null }>();

const site = useSite();
</script>

<template>
    <article class="mx-auto w-full max-w-4xl">
        <PageHeader
            title="Spencers Wood Market"
            eyebrow="Farmers & Community Market"
            image="/images/village-hall/market.jpg"
            image-position="object-[center_80%]"
            image-alt="Market stalls under gazebos outside Spencers Wood Village Hall on a sunny day"
            :occurrence="data.occurrence"
        />

        <div class="prose mt-10 max-w-none prose-stone dark:prose-invert">
            <p>
                Held every 3rd Sunday of the month, 10 am – 1 pm at Spencers
                Wood Village Hall. Free entry — come along and support your
                local producers and community stalls.
            </p>
            <p>
                For stall enquiries contact
                <a :href="`mailto:${site.marketEmail}`">{{
                    site.marketEmail
                }}</a
                >.
            </p>
        </div>

        <a
            :href="site.social.market_facebook"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-8 inline-flex rounded-full bg-teal-700 px-5 py-3 text-sm font-bold text-white hover:bg-teal-800"
            >Follow us on Facebook →</a
        >
    </article>
</template>
