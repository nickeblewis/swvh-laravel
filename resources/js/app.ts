import { createInertiaApp } from '@inertiajs/vue3';
import SiteLayout from '@/layouts/SiteLayout.vue';

const siteName = 'Spencers Wood Village Hall';

void createInertiaApp({
    // Pages pass their full title (see App\Support\Seo), so it is used as is.
    title: (title) => title || siteName,
    layout: () => SiteLayout,
    progress: {
        color: '#0f766e',
    },
});
