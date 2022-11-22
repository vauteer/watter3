<script setup>
import { computed, ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm, Head } from "@inertiajs/inertia-vue3";
import MyTextInput from '@/Shared/MyTextInput.vue';
import MyButton from '@/Shared/MyButton.vue';
import MyCheckBox from '@/Shared/MyCheckBox.vue';
import MyConfirmation from "@/Shared/MyConfirmation.vue";

let props = defineProps({
    origin: String,
    tournament: Object,
    deletable: Boolean,
});

let form = useForm({
    name: '',
    start: null,
    rounds: '3',
    games: '4',
    winpoints: '11',
    private: false,
});

let showConfirmation = ref(false);
let drawn = ref(false);

onMounted(() => {
    if (props.tournament !== undefined) {
        form.name = props.tournament.name;
        form.start = props.tournament.start;
        form.rounds = props.tournament.rounds;
        form.games = String(props.tournament.games);
        form.winpoints = String(props.tournament.winpoints);
        form.private = Boolean(props.tournament.private);

        drawn.value = Boolean(props.tournament.drawn)
    }

    document.getElementById('name').focus();
});

let submit = () => {
    if (editMode.value) {
        form.put(`/tournaments/${props.tournament.id}`);
    } else {
        form.post('/tournaments');
    }
};

let deleteEntity = () => {
    showConfirmation.value = false;
    Inertia.delete(`/tournaments/${props.tournament.id}`);
};

const editMode = computed(() => props.tournament !== undefined);
const title = computed(() => editMode.value ? "Turnier bearbeiten" : "Neues Turnier");
const submitButtonText = computed(() => editMode.value ? "Speichern" : "Hinzufügen");

</script>

<template>
    <Head :title="$t('Turniere')" />

    <div>
        <button
            tabindex="-1"
            class="hidden md:block fixed z-20 inset-0 h-full w-full bg-black opacity-50 cursor-default"
        ></button>
        <div class="relative z-30 w-full max-w-xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2">
            <div class="sm:px-2 lg:px-4 sm:py-2 lg:py-4">
                <div class="font-medium text-lg text-gray-900 ml-3 mb-4">{{ $t(title) }}</div>

                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg sm:px-2 lg:px-4 bg-white">
                    <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                        <div class="space-y-8 divide-y divide-gray-200 my-3 mx-2">
                            <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                <MyTextInput class="sm:col-span-3" v-model="form.name" :error="form.errors.name" id="name"
                                           :label="$t('Name')" autofocus
                                />
                                <MyTextInput class="sm:col-span-3" v-model="form.start" :error="form.errors.start" id="start"
                                           type='datetime-local' :label="$t('Start')" step="60"
                                />
                                <MyTextInput class="sm:col-span-3" v-model="form.rounds" :error="form.errors.rounds" id="rounds"
                                           type="number" :label="$t('Runden')" min="0" step="1"
                                           :class="{ hidden: drawn }"
                                />
                                <MyTextInput class="sm:col-span-3" v-model="form.games" :error="form.errors.games" id="games"
                                           type="number" step="1" :label="$t('Spiele (pro Runde)')"
                                           :class="{ hidden: drawn }"
                                />
                                <MyTextInput class="sm:col-span-3" v-model="form.winpoints" :error="form.errors.winpoints" id="winpoints"
                                           type="number" step="1" :label="$t('Punkte zum Sieg')"
                                           :class="{ hidden: drawn }"
                                />
                                <div class="sm:col-span-6">
                                    <MyCheckBox v-model="form.private" :error="form.errors.private"
                                              id="private" :label="$t('Privat')"
                                    />
                                </div>
                            </div>
                            <div class="py-5">
                                <div class="flex justify-between">
                                    <MyButton theme="danger" v-if="deletable" @click="showConfirmation=true">
                                        Löschen
                                    </MyButton>
                                    <div class="w-full flex justify-end">
                                        <MyButton theme="abort" @click="Inertia.get(origin)">
                                            {{ $t('Abbrechen') }}
                                        </MyButton>
                                        <MyButton type="submit" class="ml-2" :disabled="form.processing">
                                            {{ submitButtonText }}
                                        </MyButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <MyConfirmation v-if="showConfirmation" @canceled="showConfirmation = false" @confirmed="deleteEntity">
        {{ `Turnier '${tournament.name}' löschen`}}
    </MyConfirmation>
</template>
