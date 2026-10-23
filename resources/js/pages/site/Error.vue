<script setup lang="ts">
import { computed } from 'vue';

// Rendered for public 404s and 500s (see bootstrap/app.php).
const props = defineProps<{ status: number }>();

const notFound = computed(() => props.status === 404);
</script>

<template>
    <section
        class="mx-auto w-full max-w-2xl rounded-3xl bg-teal-900 p-8 text-white sm:p-12"
    >
        <p class="text-sm font-bold tracking-[0.2em] text-teal-200 uppercase">
            {{ status }}
        </p>
        <h1 class="mt-3 text-4xl font-black">
            {{
                notFound ? "We can't find that page." : 'Something went wrong.'
            }}
        </h1>
        <p class="mt-5 leading-7 text-teal-50">
            {{
                notFound
                    ? 'It may have moved, or the link may be out of date.'
                    : 'Please try again in a moment.'
            }}
        </p>
        <a
            href="/"
            class="mt-8 inline-flex rounded-full bg-white px-5 py-3 text-sm font-bold text-teal-900"
        >
            Return home
        </a>
    </section>
</template>
