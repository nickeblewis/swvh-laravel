<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ContactForm from '@/components/site/ContactForm.vue';
import ContactSent from '@/components/site/ContactSent.vue';
import LocationMap from '@/components/site/LocationMap.vue';
import PageHeader from '@/components/site/PageHeader.vue';
import { useSite } from '@/composables/useSite';

const site = useSite();
const page = usePage();

const sent = computed(() => page.props.enquirySent);
</script>

<template>
    <ContactSent v-if="sent" />
    <article v-else class="mx-auto w-full max-w-5xl">
        <PageHeader title="Contact" />

        <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_1.5fr]">
            <dl class="grid content-start gap-6">
                <div>
                    <dt
                        class="text-sm font-bold tracking-wider text-stone-500 uppercase"
                    >
                        Email
                    </dt>
                    <dd class="mt-2">
                        <a
                            :href="`mailto:${site.email}`"
                            class="font-semibold text-teal-700 hover:underline dark:text-teal-300"
                            >{{ site.email }}</a
                        >
                    </dd>
                </div>
                <div>
                    <dt
                        class="text-sm font-bold tracking-wider text-stone-500 uppercase"
                    >
                        Address
                    </dt>
                    <dd class="mt-2 leading-7">
                        <address class="not-italic">
                            {{ site.name
                            }}<template
                                v-for="line in site.address"
                                :key="line"
                            >
                                <br />{{ line }}
                            </template>
                        </address>
                    </dd>
                </div>
            </dl>
            <ContactForm />
        </div>

        <div class="mt-12">
            <LocationMap />
        </div>
    </article>
</template>
