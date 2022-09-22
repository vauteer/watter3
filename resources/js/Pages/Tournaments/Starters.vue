<script setup>
import { computed, ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/inertia-vue3";
import { Head } from '@inertiajs/inertia-vue3';
import MyLayout from '@/Shared/MyLayout.vue';
import MyButton from '@/Shared/MyButton.vue';
import MyLookahead from "@/Shared/MyLookahead.vue";
import MyConfirmation from "@/Shared/MyConfirmation.vue";

let props = defineProps({
    origin: String,
    tournamentId: Number,
    singles: Object,
    teams: Object,
    playerCount: Number,
    players: Object,
});

let form = useForm({
    player1: '',
    player2: '',
});

let connectForm = useForm({
    checkedPlayers: [],
})

onMounted(() => {
    form.player1 = '';
    form.player2 = '';
    document.getElementById('player1').focus();
});

let submit = () => {
    form.post(route('tournaments.players.store', props.tournamentId), {
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
   return props.singles.length === 0 && props.playerCount > 8 && (props.playerCount % 4 === 0);
});

let submitConnect = () => {
    connectForm.post(route('tournaments.players.connect', props.tournamentId), {
            onSuccess: () => {
                connectForm.reset();
                document.getElementById('player1').focus();
            }
        }
    );
};

let deletablePlayer = ref(null);

let playerName = computed(() => {
    return props.singles.find(item => item.id === deletablePlayer.value)?.name;
});

let deletePlayer = () => {
    const playerId = deletablePlayer.value;
    deletablePlayer.value = null;

    if (playerId != null) {
        Inertia.delete(route('tournaments.players.destroy', [props.tournamentId, playerId]));
    }
};

let deletableTeam = ref(null);

let teamName = computed(() => {
    const team = props.teams.find(item => item.id === deletableTeam.value);

    return team ? `${team.player1}/${team.player2}` : null;
});

let deleteTeam = () => {
    const teamId = deletableTeam.value;
    deletableTeam.value = null;

    if (teamId != null) {
        Inertia.delete(route('tournaments.teams.destroy', [props.tournamentId, teamId]));
    }
};

let confirmDraw = ref(false);

let draw = () => {
    confirmDraw.value = false;
    Inertia.post(route('tournaments.draw', props.tournamentId));
}

</script>

<template>
    <Head :title="$t('Registrierung')" />

    <MyLayout>
        <div>
            <button
                tabindex="-1"
                class="hidden md:block fixed z-20 inset-0 h-full w-full bg-black opacity-50 cursor-default"
            ></button>
            <div class="relative z-30 w-full max-w-2xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2">
                <div class="sm:px-2 lg:px-4 sm:py-2 lg:py-4">
                    <div class="font-medium text-lg text-gray-900ml-3 mb-2">{{ teams.length }} Teams - {{ singles.length }} {{ $t('freie Spieler') }}</div>
                    <div class="h-36 shadow ring-1 ring-black ring-opacity-5 md:rounded-lg sm:px-2 lg:px-4 bg-white">
                        <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                            <div class="space-y-8 divide-y divide-gray-200 my-3 mx-2">
                                <div class="grid grid-cols-1 gap-y-4 gap-x-4 bottom-0 sm:grid-cols-8 py-2">
                                    <MyLookahead class="sm:col-span-3" v-model="form.player1" :error="form.errors.player1"
                                                 :options="players" label="Spieler 1" :nullable="false" id="player1" />
                                    <MyLookahead class="sm:col-span-3" v-model="form.player2" :error="form.errors.player2"
                                                 :options="players" label="Spieler 2" :nullable="true" id="player2" />
                                    <MyButton type="submit" class="h-10 sm:col-span-2 mt-5 mb-3" :disabled="form.processing">
                                        Speichern
                                    </MyButton>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div v-if="singles.length > 0" class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg mt-4">
                        <div class="text-center font-semibold">{{ $t('Einzelne Spieler') }}</div>
                        <form @submit.prevent="submitConnect" class="space-y-8 divide-y divide-gray-200">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"></th>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                        {{ $t('Name') }}</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-6">
                                        <span class="sr-only">Delete</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="(player, index) in singles" :key="index" class="text-gray-500">
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
                                        <MyButton theme="danger" @click="deletablePlayer=player.id">
                                            {{ $t('Löschen') }}
                                        </MyButton>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="flex justify-center bg-white">
                                <MyButton v-if="canConnect" type="submit" :disabled="connectForm.processing"
                                              class="w-1/2 my-2"
                                >
                                    {{ $t('Team erstellen') }}
                                </MyButton>
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
                                    <MyButton theme="danger" @click="deletableTeam=team.id">
                                        Löschen
                                    </MyButton>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="w-full flex justify-between mt-3">
                        <MyButton theme="abort" @click="Inertia.get(origin)">
                            {{ $t('Zurück') }}
                        </MyButton>
                        <MyButton v-if="canDraw" @click="confirmDraw=true">
                            {{ $t("Spielplan erstellen") }}
                        </MyButton>
                    </div>
                </div>
            </div>
        </div>
        <MyConfirmation v-if="deletablePlayer" @canceled="deletablePlayer=null" @confirmed="deletePlayer"
                        subText="Soll der Spieler vom Turnier gelöscht werden?"
        >
            {{ `Spieler ${playerName} löschen`}}
        </MyConfirmation>
        <MyConfirmation v-if="deletableTeam" @canceled="deletableTeam=null" @confirmed="deleteTeam"
                        subText="Soll das Team vom Turnier gelöscht werden?"
        >
            {{ `Team ${teamName} löschen`}}
        </MyConfirmation>
        <MyConfirmation v-if="confirmDraw" @canceled="confirmDraw=false" @confirmed="draw"
                        subText="Soll der Spielplan (neu) erstellt werden?"
        >
            Spielplan erstellen
        </MyConfirmation>
    </MyLayout>
</template>
