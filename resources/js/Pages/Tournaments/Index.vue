<script setup>
import Layout from '@/Shared/Layout.vue';
import {Inertia} from "@inertiajs/inertia";
import {computed, ref, watch} from "vue";
import { Head, Link } from '@inertiajs/inertia-vue3';
import Category from '@/Shared/Category.vue';
import Pagination from '@/Shared/Pagination.vue';
import { PencilIcon, EyeSlashIcon, UserPlusIcon } from '@heroicons/vue/24/outline';
import {throttle} from "lodash";

let props = defineProps({
    auth: Object,
    tournaments: Object,
    filters: Object,
    canCreate: Boolean,
});

let search = ref(props.filters.search);

watch(search, throttle(function (value) {
    Inertia.get(route('tournaments'), {search: value}, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head :title="$t('Turniere')" />

    <Layout>
        <div class="w-full max-w-2xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2 px-4 sm:px-6 lg:px-8">
            <Category :createUrl="canCreate ? '/tournaments/create' : null" v-model="search">{{ $t('Turniere') }}</Category>

            <div class="mt-4 mb-4 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="min-w-full max-w-2xl py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th scope="col" class="pr-3 py-3.5 text-sm font-semibold text-gray-900 sm:pl-6">
                                        Datum
                                    </th>
                                    <th scope="col" class="pr-3 py-3.5 text-sm font-semibold text-gray-900 pl-4 sm:pl-6">Name</th>
                                    <th scope="col" class="px-3 py-3.5 w-6"><span class="sr-only">Status</span></th>
                                    <th scope="col" class="pr-3 py-3.5 pl-4">
                                        <span class="sr-only">Spieler hinzufügen</span>
                                    </th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-6">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="tournament in tournaments.data" :key="tournament.id"
                                    class="text-base text-gray-500 text-left">
                                    <td class="w-32 py-2 pl-4 sm:pl-6 text-sm">
                                        <div>{{ tournament.date }}</div>
                                    </td>
                                    <td class="whitespace-nowrap py-1 pl-4 text-sm sm:pl-6">
                                        <Link :href="`/tournaments/${tournament.id}/show`" as="button"
                                              class="font-semibold text-blue-500"
                                        >{{ tournament.name }}</Link>
                                        <div v-if="auth.user?.admin" class="font-sm">{{ tournament.creator }}</div>
                                    </td>
                                    <td class="px-3">
                                        <EyeSlashIcon v-if="tournament.private" class="h-5 w-5" />
                                    </td>
                                    <td class="px-3">
                                        <div class="h-5">
                                            <Link v-if="tournament.modifiable && !tournament.started" :href="`/tournaments/${tournament.id}/players`">
                                                <UserPlusIcon class="h-5 w-5 text-blue-500" />
                                            </Link>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <div class="h-5">
                                            <Link v-if="tournament.modifiable" :href="`/tournaments/${tournament.id}/edit`">
                                                <PencilIcon class="h-5 w-5 text-blue-500" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div v-if="tournaments.data.length === 0"
                                 class="text-gray-600 text-sm font-semibold ml-2"
                            >
                                Keine Daten
                            </div>
                            <div v-if="tournaments.meta.last_page > 1"
                                 class="flex justify-center bg-white" >
                                <Pagination :meta="tournaments.meta"></Pagination>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
