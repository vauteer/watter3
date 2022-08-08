<script setup>
import { computed, ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { Head, Link } from '@inertiajs/inertia-vue3';
import { PencilIcon, LockClosedIcon } from '@heroicons/vue/outline';
import Category from "@/Shared/Category.vue";
import Layout from "@/Shared/Layout.vue";

let props = defineProps({
    tournament: Object,
    currentRound: Number,
    fixtures: Object,
    canCreate: Boolean,
});

let getTitle = computed(() => {
    let date = new Date(props.tournament.start);

    return date.toLocaleDateString() + ' ' + props.tournament.name;
})
let switchRound = (round) => {
    Inertia.get(`/tournaments/${props.tournament.id}/show?round=${round}`);
};

let rowColor = (game) => {
    if (game.startsWith('11'))
        return 'bg-green-100 text-green-800';
    else
        return 'bg-red-100 text-red-800';
}

let getMarkedScore = (score) => {
  return score.replace('11', '<span class="text-green-500">11</span>');
};
</script>

<template>
        <Head :title="`Runde ${currentRound}`"/>

        <Layout>
            <div class="w-full mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2 px-4 sm:px-6 lg:px-8">
                <div class="w-full text-2xl font-medium text-left text-gray-900 pt-3 pl-2">{{ getTitle }}</div>

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
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden md:block">
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
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="fixture in fixtures.data" :key="fixture.id" class="text-gray-700">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-center sm:pl-6">
                                            <div class="font-bold">{{ fixture.table }}</div>
                                        </td>
                                        <td class="px-3">
                                            <div class="font-bold">{{ fixture.team1 }}</div>
                                            <div class="font-bold">{{ fixture.team2 }}</div>
                                        </td>
                                        <td class="px-3 flex justify-left pt-2.5 hidden md:block">
                                            <span v-for="game in fixture.games"
                                                  class="inline-flex items-center px-3.5 py-2 rounded-full text-xs font-medium ml-2"
                                                  :class="rowColor(game)"
                                            > {{ game }} </span>
                                        </td>
                                        <td class="px-3 text-center">
                                            <div class="font-bold text-gray-900">{{ fixture.scoreGames }}</div>
                                            <div class="font-semibold">{{ fixture.scorePoints }}</div>
                                        </td>
                                        <td class="px-3">
                                            <div class="h-5">
                                                <Link v-if="fixture.editable" :href="`/tournaments/fixtures/${fixture.id}/edit`">
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
                                <nav class="flex items-center justify-center py-3" aria-label="Progress">
                                    <p class="text-base font-medium">Runde {{ currentRound }} von {{ tournament.rounds }}</p>
                                    <ol role="list" class="ml-8 flex items-center space-x-5">
                                        <li v-for="index in tournament.rounds" :key="index">
                                            <div v-if="index === currentRound" class="relative flex items-center justify-center" aria-current="round">
                                                  <span class="absolute w-5 h-5 p-px flex" aria-hidden="true">
                                                    <span class="w-full h-full rounded-full bg-indigo-200" />
                                                  </span>
                                                <span class="relative block w-2.5 h-2.5 bg-indigo-600 rounded-full" aria-hidden="true" />
                                                <span class="sr-only">Runde {{ currentRound }}</span>
                                            </div>
                                            <button v-else @click="switchRound(index)" class="block w-2.5 h-2.5 bg-gray-200 rounded-full hover:bg-gray-400">
                                                <span class="sr-only">Runde {{ currentRound }}</span>
                                            </button>
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
</template>
