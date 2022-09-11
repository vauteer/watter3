<script setup>
import MyLayout from '@/Shared/MyLayout.vue';
import {Inertia} from "@inertiajs/inertia";
import {computed, ref, watch} from "vue";
import { Head, Link } from '@inertiajs/inertia-vue3';
import MyCategory from '@/Shared/MyCategory.vue';
import MyPagination from '@/Shared/MyPagination.vue';
import { PencilIcon, LockClosedIcon } from '@heroicons/vue/24/outline';
import {throttle} from "lodash";

let props = defineProps({
    players: Object,
    options: Object,
});

let showTournaments = (id) => {
    let filter = `playedBy_${id}`
    Inertia.get(route('tournaments'), {
            filter: filter,
        },
        {
            preserveState: true,
            replace: true,
        });
};

let search = ref(props.options.search);

watch(search, throttle(function (value) {
    Inertia.get('/players', {search: value}, {
        preserveState: true,
        replace: true,
    });
}, 300));
</script>

<template>
    <Head :title="$t('Spieler')" />

    <MyLayout>
        <div class="w-full max-w-2xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2 px-4 sm:px-6 lg:px-8">
            <MyCategory createUrl="/players/create" v-model="search">{{ $t('Spieler') }}</MyCategory>

            <div class="mt-4 mb-4 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="min-w-full max-w-2xl py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                    <th scope="col" class="px-3 py-3.5 w-6"><span class="sr-only">Show Tournaments</span></th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-6">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="player in players.data" :key="player.id" class="text-gray-500">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="font-bold">{{ player.name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-blue-500 sm:pl-6">
                                        <div v-if="player.hasTournaments">
                                            <a class="cursor-pointer" @click="showTournaments(player.id)" as="button">Turniere</a>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <div class="h-5">
                                            <Link v-if="player.modifiable" :href="`/players/${player.id}/edit`">
                                                <PencilIcon class="h-5 w-5 text-blue-500" />
                                            </Link>
                                            <LockClosedIcon v-else class="h-5 w-5" />
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div v-if="players.data.length === 0"
                                 class="text-gray-600 text-sm font-semibold ml-2"
                            >
                                Keine Daten
                            </div>
                            <div v-if="players.meta.last_page > 1"
                                 class="flex justify-center bg-white" >
                                <MyPagination :meta="players.meta"></MyPagination>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MyLayout>
</template>
