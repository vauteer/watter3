<script setup>
import {computed} from "vue";
import {Inertia} from "@inertiajs/inertia";
import {Head, Link} from '@inertiajs/inertia-vue3';
import {PencilIcon} from '@heroicons/vue/24/outline';
import Layout from "@/Shared/Layout.vue";
import Tabs from "@/Shared/Tabs.vue";

let props = defineProps({
    auth: Object,
    tournament: Object,
    currentRound: Number,
    fixtures: Object,
    standings: Object,
    canCreate: Boolean,
    canEdit: Boolean,
    tabsModel: Object,
});

let getTitle = computed(() => {
    let date = new Date(props.tournament.start);

    return date.toLocaleDateString() + ' ' + props.tournament.name;
})

let getTabsModel = computed(() => {
    let model = {};

    for (let i = 1; i <= props.tournament.rounds; i++) {
        model[i] = 'Runde ' + i;
    }

    return model;
});

let switchRound = (round) => {
    Inertia.get(`/tournaments/${props.tournament.id}/show?round=${round}`);
};

</script>

<template>
    <Head title="Turniere"/>

    <Layout>
        <div
            class="w-full mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2 px-4 sm:px-6 lg:px-8">
            <div class="w-full flex justify-between pt-3 pl-2">
                <div class="text-xl font-semibold text-gray-900">{{ getTitle }}</div>
                <a v-if="auth.user"
                    class="bg-blue-500 py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-center text-gray-700 hover:bg-blue-200 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-indigo-500"
                    :href="`/tournaments/${tournament.id}/lists/${currentRound}`" target="_blank">Tisch-Listen</a>
            </div>
            <div class="mt-4 mb-4 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 text-center sm:pl-6">
                                        Tisch
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Paarung
                                    </th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden md:block">
                                        Ergebnis
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                        <div>Spiele</div>
                                        <div>Punkte</div>
                                    </th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-6">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white text-gray-700">
                                <tr v-for="(fixture, index) in fixtures.data" :key="fixture.tableNumber"
                                    :class="index % 2 ? '' : 'bg-gray-100'"
                                >
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-center sm:pl-6">
                                        <div class="font-bold">{{ fixture.tableNumber }}</div>
                                    </td>
                                    <td class="px-3">
                                        <div class="font-bold text-green-500">{{ fixture.team1 }}</div>
                                        <div class="font-bold text-blue-500">{{ fixture.team2 }}</div>
                                    </td>
                                    <td class="px-3 flex justify-left pt-2.5 hidden md:block">
                                            <span v-for="game in fixture.games"
                                                  class="inline-flex items-center px-3.5 py-2 rounded-full text-xs font-medium ml-2"
                                                  :class="game.startsWith(tournament.winpoints) ? 'bg-green-100 text-green-800' :'bg-blue-100 text-blue-800'"
                                            > {{ game }} </span>
                                    </td>
                                    <td class="px-3 text-center">
                                        <div class="font-bold text-gray-900">{{ fixture.scoreGames }}</div>
                                        <div class="font-semibold">{{ fixture.scorePoints }}</div>
                                    </td>
                                    <td class="px-3">
                                        <div class="h-5">
                                            <Link v-if="canEdit"
                                                  :href="`/tournaments/fixtures/${fixture.id}/edit`">
                                                <PencilIcon class="h-5 w-5 text-blue-500"/>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div v-if="fixtures.data.length === 0"
                                 class="text-gray-600 text-sm font-semibold ml-2"
                            >
                                Keine Daten
                            </div>
                        </div>
                        <nav class="flex items-center justify-center mx-3 mt-3">
                            <Tabs class="w-full max-w-md"
                                  :model="getTabsModel"
                                  :onClicked="switchRound"
                                  :selected-index="currentRound - 1"
                            ></Tabs>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="mt-4 mb-4 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="w-6 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 text-center sm:pl-6">
                                        Rang
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Team
                                    </th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 hidden md:table-cell">
                                        Punkte
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                        Differenz
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">
                                        Spiele
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white text-gray-700">
                                <tr v-for="(standing, rank) in standings" :key="rank"
                                    :class="rank % 2 ? '' : 'bg-gray-100'"
                                >
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-center sm:pl-6">
                                        <div class="font-bold">{{ rank + 1 }}</div>
                                    </td>
                                    <td class="px-3 font-bold">
                                        <div>{{ standing.player1 }}</div>
                                        <div>{{ standing.player2 }}</div>
                                    </td>
                                    <td class="px-3 pt-2.5 text-center hidden md:table-cell">
                                        <div>{{ standing.pointsWon }} : {{ standing.pointsLost }}</div>
                                    </td>
                                    <td class="px-3 text-center">
                                        <div class="font-semibold">{{ standing.pointsWon - standing.pointsLost }}</div>
                                    </td>
                                    <td class="px-3 text-center">
                                        <div class="font-semibold">{{ standing.won }} : {{ standing.lost }}</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <p class="pl-2 py-2 text-center">Sortiert nach gewonnenen Spielen, Punktedifferenz, Punkteanzahl</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
