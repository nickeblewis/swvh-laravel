<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import ThemeToggle from '@/components/site/ThemeToggle.vue';
import { useSite } from '@/composables/useSite';

const site = useSite();
const page = usePage();
const year = new Date().getFullYear();

const navigation = [
    { label: 'Activities', to: '/regular-activities' },
    { label: 'Market', to: '/market' },
    { label: 'Repair Cafe', to: '/repair-cafe' },
    { label: 'Booking', to: '/booking' },
    { label: 'Articles', to: '/articles' },
    { label: 'About', to: '/about' },
    { label: 'Contact', to: '/contact' },
];

const footerNavigation = [
    { label: 'Regular Activities', to: '/regular-activities' },
    { label: 'Market', to: '/market' },
    { label: 'Repair Cafe', to: '/repair-cafe' },
    { label: 'Articles', to: '/articles' },
    { label: 'Terms', to: '/terms-and-conditions' },
];

// The server prints the title, description and canonical link into the HTML
// (see App\Support\Seo). This keeps them right when moving between pages
// without a full reload.
function syncHead() {
    const meta = page.props.meta;

    if (!meta) {
        return;
    }

    document.title = meta.title;
    document
        .querySelector('meta[name="description"]')
        ?.setAttribute('content', meta.description);
    document
        .querySelector('link[rel="canonical"]')
        ?.setAttribute('href', meta.canonical);
}

let stopListening: (() => void) | undefined;

onMounted(() => {
    stopListening = router.on('navigate', syncHead);
});

onUnmounted(() => stopListening?.());
</script>

<template>
    <!-- Header, page and footer share one grid, so `gap-10` sets the rhythm between a page's top-level sections. -->
    <main
        class="mx-auto grid max-w-7xl gap-10 overflow-hidden px-5 py-6 sm:px-8 lg:px-10"
    >
        <header
            class="flex flex-col gap-5 border-b border-stone-200 pb-5 sm:flex-row sm:items-center sm:justify-between dark:border-stone-800"
        >
            <Link
                href="/"
                class="flex items-center gap-3 text-xl font-black tracking-tight text-stone-950 dark:text-white"
            >
                <span
                    class="grid size-12 place-items-center rounded-full bg-teal-700 text-xs text-white"
                    >{{ site.shortName }}</span
                >
                <span>{{ site.name }}</span>
            </Link>
            <nav
                class="flex flex-wrap items-center gap-x-5 gap-y-3 text-sm font-semibold text-stone-600 dark:text-stone-300"
            >
                <Link
                    v-for="item in navigation"
                    :key="item.to"
                    :href="item.to"
                    class="hover:text-teal-700 dark:hover:text-teal-300"
                >
                    {{ item.label }}
                </Link>
                <ThemeToggle />
            </nav>
        </header>

        <slot />

        <footer
            class="grid gap-6 border-t border-stone-200 py-8 text-sm text-stone-500 sm:grid-cols-2 dark:border-stone-800 dark:text-stone-400"
        >
            <div>
                <p class="font-bold text-stone-900 dark:text-stone-100">
                    {{ site.name }}
                </p>
                <p class="mt-1">
                    A community hall for activities, events and private hire.
                </p>
                <p class="mt-3">
                    &copy; {{ year }}
                    <a
                        href="https://headforcode.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold hover:text-teal-700 dark:hover:text-teal-300"
                        >HeadForCode</a
                    >. All rights reserved.
                </p>
            </div>
            <nav class="flex flex-wrap gap-x-5 gap-y-2 sm:justify-end">
                <Link
                    v-for="item in footerNavigation"
                    :key="item.to"
                    :href="item.to"
                    class="hover:text-teal-700 dark:hover:text-teal-300"
                >
                    {{ item.label }}
                </Link>
            </nav>
        </footer>
    </main>
</template>
