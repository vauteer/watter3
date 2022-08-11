<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3';
import Layout from '@/Shared/Layout.vue';
import TextInput from '@/Shared/TextInput.vue';
import SubmitButton from '@/Shared/SubmitButton.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password" />
    <Layout>
        <div class="min-h-full flex flex-col justify-center sm:px-6 lg:px-8 bg-gray-100">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Neues Passwort</h2>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                    <form @submit.prevent="submit" class="space-y-6" action="#" method="POST">
                        <TextInput class="sm:col-span-6" v-model="form.email"
                                   :error="form.errors.email" id="email" type="email"
                                   label="Email" required autofocus autocomplete="username" />
                        <TextInput v-model="form.password"
                                   :error="form.errors.password" id="password"
                                   label="Passwort" type="password"
                                   required autocomplete="new-password"
                        />
                        <TextInput v-model="form.password_confirmation"
                                   :error="form.errors.password_confirmation" id="password_confirmation"
                                   label="Passwort bestätigen" type="password"
                                   required autocomplete="new-password"
                        />

                        <SubmitButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full">
                            Passwort setzen
                        </SubmitButton>
                    </form>
                </div>
            </div>
        </div>
    </Layout>
</template>
