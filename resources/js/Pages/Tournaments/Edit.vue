<script setup>
import { computed, ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm, Head } from "@inertiajs/inertia-vue3";
import Layout from '@/Shared/Layout.vue';
import EditTitle from '@/Shared/EditTitle.vue'
import TextInput from '@/Shared/TextInput.vue';
import AbortButton from '@/Shared/AbortButton.vue';
import SubmitButton from '@/Shared/SubmitButton.vue';
import DeleteButton from '@/Shared/DeleteButton.vue';
import CheckBox from '@/Shared/CheckBox.vue';

let props = defineProps({
    tournament: Object,
});

let form = useForm({
    name: '',
    start: null,
    rounds: '3',
    games: '4',
    winpoints: '11',
    private: false,
});

let editMode = ref(false);
let drawn = ref(false);

onMounted(() => {
    if (props.tournament !== undefined) {
        form.name = props.tournament.name;
        form.start = props.tournament.start;
        form.rounds = props.tournament.rounds;
        form.games = String(props.tournament.games);
        form.winpoints = String(props.tournament.winpoints);
        form.private = Boolean(props.tournament.private);

        editMode.value = true;
        drawn.value = Boolean(props.tournament.drawn)
    }

    document.getElementById('name').focus();
});

let submit = () => {
    if (editMode.value === true) {
        form.put(`/tournaments/${props.tournament.id}`);
    } else {
        form.post('/tournaments');
    }
};

let deleteTournament = () => {
    if (confirm('Wollen Sie das Turnier wirklich löschen ?')) {
        Inertia.delete(`/tournaments/${props.tournament.id}`);
    }
};

const getTitle = computed(() => {
    return editMode.value ? "Turnier bearbeiten" : "Neues Turnier";
});

const getSubmitButtonText = computed(() => {
    return editMode.value ? "Speichern" : "Hinzufügen";
});

</script>

<template>
    <Head title="Turniere" />

    <Layout>
        <div>
            <button
                tabindex="-1"
                class="hidden md:block fixed z-20 inset-0 h-full w-full bg-black opacity-50 cursor-default"
            ></button>
            <div class="relative z-30 w-full max-w-xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2">
                <div class="sm:px-2 lg:px-4 sm:py-2 lg:py-4">
                    <EditTitle class="ml-3 mb-4">{{ getTitle }}</EditTitle>

                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg sm:px-2 lg:px-4 bg-white">
                        <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                            <div class="space-y-8 divide-y divide-gray-200 my-3 mx-2">
                                <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                    <TextInput class="sm:col-span-3" v-model="form.name" :error="form.errors.name" id="name"
                                               label="Name"/>
                                    <TextInput class="sm:col-span-3" v-model="form.start" :error="form.errors.start" id="start"
                                               type='datetime-local' label="Start" step="60"/>
                                    <TextInput class="sm:col-span-3" v-model="form.rounds" :error="form.errors.rounds" id="rounds"
                                               type="number" label="Runden" min="0" step="1"
                                               :class="{ hidden: drawn }"
                                    />
                                    <TextInput class="sm:col-span-3" v-model="form.games" :error="form.errors.games" id="games"
                                               type="number" step="1" label="Spiele (pro Runde)"
                                               :class="{ hidden: drawn }"
                                    />
                                    <TextInput class="sm:col-span-3" v-model="form.winpoints" :error="form.errors.winpoints" id="winpoints"
                                               type="number" step="1" label="Punkte zum Sieg"
                                               :class="{ hidden: drawn }"
                                    />
                                    <div class="sm:col-span-6">
                                        <CheckBox v-model="form.private" :error="form.errors.private"
                                                  id="private" label="Privat"/>
                                    </div>
                                </div>
                                <div class="py-5">
                                    <div class="flex justify-between">
                                        <DeleteButton v-if="editMode" :onDelete="deleteTournament"/>
                                        <div class="w-full flex justify-end">
                                            <AbortButton :href="route('tournaments')">
                                                Abbrechen
                                            </AbortButton>
                                            <SubmitButton class="ml-2" :title="getSubmitButtonText"
                                                          :disabled="form.processing"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
