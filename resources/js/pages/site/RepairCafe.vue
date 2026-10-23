<script setup lang="ts">
import PageHeader from '@/components/site/PageHeader.vue';
import PhotoGrid from '@/components/site/PhotoGrid.vue';
import { useSite } from '@/composables/useSite';
import type { GalleryPhoto, Occurrence } from '@/types/content';

const site = useSite();

// The badge under the title comes from the cafe's entry in Regular Activities.
// The photos are every image in public/images/repair-cafe (App\Support\Gallery).
const data = defineProps<{
    occurrence: Occurrence | null;
    photos: GalleryPhoto[];
}>();
const photos = data.photos;
</script>

<template>
    <article class="mx-auto w-full max-w-4xl">
        <PageHeader
            title="Spencers Wood Village Hall Repair Cafe"
            eyebrow="Repair Cafe"
            :occurrence="data.occurrence"
        />

        <div
            class="prose prose-xl mt-10 max-w-none prose-stone dark:prose-invert"
        >
            <p>
                Spencers Wood Village Hall Repair Cafe is a friendly group of
                expert volunteers that run a session once a month at the Village
                Hall, open the first Sunday of the month from 10 am to 1 pm.
            </p>
        </div>

        <PhotoGrid :photos="photos" />

        <div class="prose mt-10 max-w-none prose-stone dark:prose-invert">
            <p>
                We will try to repair any household items that may otherwise be
                destined for landfill. Our ethos is very much reduce, reuse,
                recycle and our volunteers are happy to share their skills.
            </p>
            <p>
                We do not charge for our service, but very much appreciate
                donations. Donations go towards out-of-pocket costs to our
                volunteers and Spencers Wood Village Hall, a registered charity.
            </p>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a
                :href="`mailto:${site.email}`"
                class="inline-flex rounded-full bg-teal-700 px-5 py-3 text-sm font-bold text-white hover:bg-teal-800"
                >Enquiries: {{ site.email }}</a
            >
            <a
                :href="site.social.repair_cafe_facebook"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex rounded-full bg-teal-700 px-5 py-3 text-sm font-bold text-white hover:bg-teal-800"
                >Follow us on Facebook →</a
            >
        </div>
    </article>
</template>
