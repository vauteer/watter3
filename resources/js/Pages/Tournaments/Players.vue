<script setup>
import { computed, ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/inertia-vue3";
import Layout from '@/Shared/Layout.vue';
import EditTitle from '@/Shared/EditTitle.vue'
import TextInput from '@/Shared/TextInput.vue';
import AbortButton from '@/Shared/AbortButton.vue';
import SubmitButton from '@/Shared/SubmitButton.vue';
import DeleteButton from '@/Shared/DeleteButton.vue';
import CheckBox from '@/Shared/CheckBox.vue';

let props = defineProps({
    id: Number,
    players: Object,
    teams: Object,
});

let form = useForm({
    player1: '',
    player2: '',
});

onMounted(() => {
    if (props.players !== undefined) {
    }
    form.player1 = '';
    form.player2 = '';

    document.getElementById('player1').focus();
});

let submit = () => {
    form.post(`/tournaments/${props.id}/players`, {
            onSuccess: () => form.reset()
        }
    );
};

</script>

<template>
    <Layout>
        <div>
            <button
                tabindex="-1"
                class="hidden md:block fixed z-20 inset-0 h-full w-full bg-black opacity-50 cursor-default"
            ></button>
            <div class="relative z-30 w-full max-w-xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2">
                <div class="sm:px-2 lg:px-4 sm:py-2 lg:py-4">
                    <EditTitle class="ml-3 mb-4">Spieler hinzufügen {{ players.length }}</EditTitle>

                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg sm:px-2 lg:px-4 bg-white">
                        <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                            <div class="space-y-8 divide-y divide-gray-200 my-3 mx-2">
                                <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-8">
                                    <TextInput class="sm:col-span-3" v-model="form.player1" :error="form.errors.player1"
                                        id="player1" label="Spieler 1" />
                                    <TextInput class="sm:col-span-3" v-model="form.player2" :error="form.errors.player2"
                                        id="player2" label="Spieler 2" />
                                    <SubmitButton class="sm:col-span-2" :disabled="form.processing"/>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div>Spieler</div>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
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
                            <tr v-for="player in players" :key="player.id" class="text-gray-500">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-bold">O</div>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-bold">{{ player.name }}</div>
                                </td>

                                <td class="px-3">
                                    Löschen
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>Teams</div>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
<!--                            <thead class="bg-gray-50">-->
<!--                            <tr>-->
<!--                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Spieler 1</th>-->
<!--                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Spieler 2</th>-->
<!--                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-6">-->
<!--                                    <span class="sr-only">Delete</span>-->
<!--                                </th>-->
<!--                            </tr>-->
<!--                            </thead>-->
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="team in teams" :key="team.id" class="text-gray-500">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-bold">{{ team.player1 }}</div>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-bold">{{ team.player2 }}</div>
                                </td>
                                <td class="px-3">
                                    Löschen
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
