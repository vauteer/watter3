<script setup>
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import Layout from '@/Shared/Layout.vue';
import TextInput from '@/Shared/TextInput.vue';
import SubmitButton from '@/Shared/SubmitButton.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Layout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <TextInput class="sm:col-span-6" v-model="form.name"
                       :error="form.errors.name" id="name" type="text"
                       required autocomplete="name" autofocus
                       label="Name"/>

            <TextInput class="sm:col-span-6" v-model="form.email"
                       :error="form.errors.email" id="email" type="email"
                       label="Email" required autofocus autocomplete="username"/>

            <TextInput class="sm:col-span-6" v-model="form.password"
                       :error="form.errors.password" id="password" type="password"
                       required autocomplete="current-password"
                       label="Passwort"/>

            <TextInput class="sm:col-span-6" v-model="form.password_confirmation"
                       :error="form.errors.password_confirmation" id="password_confirmation" type="password"
                       required autocomplete="new-password" label="Passwort bestätigen"/>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900">
                    Already registered?
                </Link>

                <SubmitButton :disabled="form.processing" class="w-full">
                    Register
                </SubmitButton>
            </div>
        </form>
    </Layout>
</template>
