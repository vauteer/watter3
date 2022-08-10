<script setup>
import { computed, ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/inertia-vue3";
import { Head, Link } from '@inertiajs/inertia-vue3';
import Layout from '@/Shared/Layout.vue';
import EditTitle from '@/Shared/EditTitle.vue'
import TextInput from '@/Shared/TextInput.vue';
import AbortButton from '@/Shared/AbortButton.vue';
import SubmitButton from '@/Shared/SubmitButton.vue';
import DeleteButton from '@/Shared/DeleteButton.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import ActionButton from "@/Shared/ActionButton.vue";

let props = defineProps({
    tournamentId: Number,
    players: Object,
    teams: Object,
    playerCount: Number,
});

let form = useForm({
    player1: '',
    player2: '',
});

let connectForm = useForm({
    checkedPlayers: [],
})

onMounted(() => {
    if (props.players !== undefined) {
    }
    form.player1 = '';
    form.player2 = '';
    document.getElementById('player1').focus();
});

let submit = () => {
    form.post(`/tournaments/${props.tournamentId}/players`, {
            onSuccess: () => {
                form.reset();
                document.getElementById('player1').focus();
            }
        }
    );
};

let canConnect = computed(() => {
    return connectForm.checkedPlayers.length === 2;
});

let canDraw = computed(() => {
   return props.players.length === 0 && props.playerCount > 8 && (props.playerCount % 4 === 0);
});

let submitConnect = () => {
    connectForm.post(`/tournaments/${props.tournamentId}/players/connect`, {
            onSuccess: () => {
                connectForm.reset();
                document.getElementById('player1').focus();
            }
        }
    );
};

let deletePlayer = (playerId) => {
    if (confirm('Wollen Sie den Spieler wirklich löschen ?')) {
        Inertia.delete(`/tournaments/${props.tournamentId}/players/${playerId}`);
    }
};

let draw = () => {
    if (confirm('Wollen Sie das Turnier wirklich (neu) auslosen ?')) {
        Inertia.post(`/tournaments/${props.tournamentId}/draw`);
    }
}

let deleteTeam = (teamId) => {
    if (confirm('Wollen Sie das Team wirklich löschen ?')) {
        Inertia.delete(`/tournaments/${props.tournamentId}/teams/${teamId}`);
    }
};


</script>

<template>
    <Head title="Registrierung" />

    <Layout>
        <div>
            <button
                tabindex="-1"
                class="hidden md:block fixed z-20 inset-0 h-full w-full bg-black opacity-50 cursor-default"
            ></button>
            <div class="relative z-30 w-full max-w-xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2">
                <div class="sm:px-2 lg:px-4 sm:py-2 lg:py-4">
                    <EditTitle class="ml-3 mb-4">{{ teams.length }} Teams - {{ players.length }} freie Spieler</EditTitle>

                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg sm:px-2 lg:px-4 bg-white">
                        <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                            <div class="space-y-8 divide-y divide-gray-200 my-3 mx-2">
                                <div class="grid grid-cols-1 gap-y-4 gap-x-4 bottom-0 sm:grid-cols-8">
                                    <TextInput class="sm:col-span-3" v-model="form.player1" :error="form.errors.player1"
                                        id="player1" label="Spieler 1" />
                                    <TextInput class="sm:col-span-3" v-model="form.player2" :error="form.errors.player2"
                                        id="player2" label="Spieler 2" />
                                    <SubmitButton class="sm:col-span-2 mt-5 mb-3" :disabled="form.processing"/>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div v-if="players.length > 0" class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg mt-4">
                        <div class="text-center font-semibold">Einzelne Spieler</div>
                        <form @submit.prevent="submitConnect" class="space-y-8 divide-y divide-gray-200">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"></th>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-6">
                                        <span class="sr-only">Delete</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="(player, index) in players" :key="index" class="text-gray-500">
                                    <td class="w-6 whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <input class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            v-model="connectForm.checkedPlayers" :value="player.id" :id="`connect_${player.id}`"
                                               type="checkbox"
                                        >
                                    </td>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="font-bold">{{ player.name }}</div>
                                    </td>

                                    <td class="w-10 px-3">
                                        <button
                                            class="bg-red-500 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            type="button"
                                            @click="deletePlayer(player.id)"
                                        >
                                            Löschen
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="flex justify-center bg-white">
                                <SubmitButton v-if="canConnect" :disabled="connectForm.processing"
                                              class="w-1/2 my-2"
                                >Team erstellen</SubmitButton>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg mt-4">
                        <div class="text-center font-semibold">Teams</div>
                        <table class="min-w-full divide-y divide-gray-300">
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="team in teams" :key="team.id" class="text-gray-500">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-bold">{{ team.player1 }}</div>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-bold">{{ team.player2 }}</div>
                                </td>
                                <td class=" w-10 px-3">
                                    <button
                                        class="bg-red-500 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        type="button"
                                        @click="deleteTeam(team.id)"
                                    >
                                        Löschen
                                    </button>

                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="w-full flex justify-between mt-3">
                        <AbortButton :href="route('tournaments')">
                            Zurück
                        </AbortButton>
                        <ActionButton v-if="canDraw" :onClick="draw">Spielplan erstellen</ActionButton>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
