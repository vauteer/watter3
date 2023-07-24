<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MyLayout from '@/Shared/MyLayout.vue';
import MyTextInput from '@/Shared/MyTextInput.vue';
import MyButton from '@/Shared/MyButton.vue';

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
    <MyLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <MyTextInput class="sm:col-span-6" v-model="form.name"
                       :error="form.errors.name" id="name" type="text"
                       required autocomplete="name" autofocus
                       label="Name"/>

            <MyTextInput class="sm:col-span-6" v-model="form.email"
                       :error="form.errors.email" id="email" type="email"
                       label="Email" required autofocus autocomplete="username"/>

            <MyTextInput class="sm:col-span-6" v-model="form.password"
                       :error="form.errors.password" id="password" type="password"
                       required autocomplete="current-password"
                       label="Passwort"/>

            <MyTextInput class="sm:col-span-6" v-model="form.password_confirmation"
                       :error="form.errors.password_confirmation" id="password_confirmation" type="password"
                       required autocomplete="new-password" label="Passwort bestätigen"/>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900">
                    Already registered?
                </Link>

                <MyButton type="submit" class="w-full" :disabled="form.processing">
                    Register
                </MyButton>
            </div>
        </form>
    </MyLayout>
</template>
