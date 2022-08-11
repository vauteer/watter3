<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3';
import Layout from '@/Shared/Layout.vue';
import TextInput from '@/Shared/TextInput.vue';
import SubmitButton from '@/Shared/SubmitButton.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Passwort vergessen" />

    <Layout>
        <div class="min-h-full flex flex-col justify-center sm:px-6 lg:px-8 bg-gray-100">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <!--                <img class="mx-auto h-12 w-auto" src="https://tailwindui.com/img/logos/workflow-mark-indigo-600.svg" alt="Workflow" />-->
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Passwort zurücksetzen</h2>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                    <div class="mb-4 text-sm text-gray-600">
                        Passwort vergessen ? Kein Problem.<br> Teilen Sie uns einfach Ihre Email-Adresse mit und wir senden Ihnen einen Reset-Link zu.
                    </div>
                    <form @submit.prevent="submit" class="space-y-6" action="#" method="POST">
                        <TextInput class="sm:col-span-6" v-model="form.email"
                                   :error="form.errors.email" id="email" type="email"
                                   label="Email" required autofocus autocomplete="username"/>

                        <SubmitButton :disabled="form.processing" class="w-full">
                            Link zusenden
                        </SubmitButton>
                    </form>
                </div>
            </div>
        </div>
    </Layout>
</template>
