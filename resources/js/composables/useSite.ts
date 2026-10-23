import { usePage } from '@inertiajs/vue3';
import type { Site } from '@/types/content';

/**
 * Site identity: name, email, address, charity number and so on, from
 * `config/site.php` via the shared `site` prop.
 *
 * Never hard-code these in a component — read them from here so there's one
 * place to change them.
 */
export function useSite(): Site {
    return usePage().props.site;
}
