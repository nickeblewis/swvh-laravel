<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useSite } from '@/composables/useSite';

/**
 * The enquiry form, used on `/contact` and on admin-created pages with the
 * `contact` template. Posts to ContactController, which emails the enquiry
 * and sends the visitor back here; the page then swaps itself for
 * `<ContactSent>`.
 */
const site = useSite();

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
    // Honeypot: hidden from users, positioned off-screen, never focusable.
    // The server drops any submission where it's filled.
    website: '',
});

const inputClass =
    'mt-2 w-full rounded-xl border border-stone-300 bg-white px-3 py-2 dark:border-stone-700 dark:bg-stone-900';

const errorClass = 'mt-1 text-sm font-semibold text-red-700 dark:text-red-300';

function submit() {
    form.post('/contact', { preserveScroll: true });
}
</script>

<template>
    <div>
        <h2 class="text-2xl font-black">Send an enquiry</h2>
        <p
            v-if="form.hasErrors"
            role="alert"
            class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 dark:bg-red-950 dark:text-red-200"
        >
            Please check the form below. If it still won't send, email us at
            {{ site.email }}.
        </p>
        <form
            name="contact"
            method="post"
            class="mt-6 grid gap-5"
            @submit.prevent="submit"
        >
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="website">Leave this field empty</label>
                <input
                    id="website"
                    v-model="form.website"
                    name="website"
                    type="text"
                    tabindex="-1"
                    autocomplete="off"
                />
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="text-sm font-bold">Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        name="name"
                        type="text"
                        required
                        autocomplete="name"
                        :class="inputClass"
                    />
                    <p v-if="form.errors.name" :class="errorClass">
                        {{ form.errors.name }}
                    </p>
                </div>
                <div>
                    <label for="email" class="text-sm font-bold">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        name="email"
                        type="email"
                        required
                        autocomplete="email"
                        :class="inputClass"
                    />
                    <p v-if="form.errors.email" :class="errorClass">
                        {{ form.errors.email }}
                    </p>
                </div>
            </div>
            <div>
                <label for="subject" class="text-sm font-bold">Subject</label>
                <input
                    id="subject"
                    v-model="form.subject"
                    name="subject"
                    type="text"
                    maxlength="200"
                    :class="inputClass"
                />
                <p v-if="form.errors.subject" :class="errorClass">
                    {{ form.errors.subject }}
                </p>
            </div>
            <div>
                <label for="message" class="text-sm font-bold">Message</label>
                <textarea
                    id="message"
                    v-model="form.message"
                    name="message"
                    required
                    rows="6"
                    maxlength="5000"
                    :class="inputClass"
                />
                <p v-if="form.errors.message" :class="errorClass">
                    {{ form.errors.message }}
                </p>
            </div>
            <button
                type="submit"
                :disabled="form.processing"
                class="w-fit cursor-pointer rounded-full bg-teal-700 px-5 py-3 text-sm font-bold text-white hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? 'Sending…' : 'Send enquiry' }}
            </button>
        </form>
    </div>
</template>
