<script setup>
import Layout from '@/Shared/Layout.vue';
import {Inertia} from "@inertiajs/inertia";
import {computed, ref, watch} from "vue";
import { Head, Link } from '@inertiajs/inertia-vue3';
import Category from '@/Shared/Category.vue';
import Pagination from '@/Shared/Pagination.vue';
import { PencilIcon, StarIcon } from '@heroicons/vue/outline';
import {throttle} from "lodash";

let props = defineProps({
    users: Object,
    filters: Object,
});

let search = ref(props.filters.search);

watch(search, throttle(function (value) {
    Inertia.get('/users', {search: value}, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head title="Benutzer" />

    <Layout>
        <div class="w-full max-w-2xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2 px-4 sm:px-6 lg:px-8">
            <Category createUrl="/users/create" v-model="search">Benutzer</Category>

            <div class="mt-4 mb-4 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="min-w-full max-w-2xl py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                    <th scope="col" class="py-3.5 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Email</th>
                                    <th scope="col" class="px-3 py-3.5 w-6"><span class="sr-only">Status</span></th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-6">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="user in users.data" :key="user.id" class="text-gray-500">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="font-bold">{{ user.name }}</div>
                                    </td>
                                    <td class="pl-2 text-sm text-gray-500 sm:pl-4">
                                        <div>{{ user.email }}</div>
                                    </td>
                                    <td class="px-3">
                                        <div class="h5">
                                            <StarIcon v-if="user.admin" class="h-5 w-5" />
                                        </div>
                                    </td>

                                    <td class="px-3">
                                        <div class="h-5">
                                            <Link v-if="user.editable" :href="`/users/${user.id}/edit`">
                                                <PencilIcon class="h-5 w-5 text-blue-500" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div v-if="users.data.length === 0"
                                 class="text-gray-600 text-sm font-semibold ml-2"
                            >
                                Keine Daten
                            </div>
                            <Pagination v-if="users.meta.last_page > 1" class="mt-6" :meta="users.meta"></Pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>