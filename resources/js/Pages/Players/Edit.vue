<script setup>
import {computed, ref, onMounted} from "vue";
import {Inertia} from "@inertiajs/inertia";
import { useForm, Head } from "@inertiajs/inertia-vue3";
import MyTextInput from '@/Shared/MyTextInput.vue';
import MyButton from '@/Shared/MyButton.vue';
import MyConfirmation from "@/Shared/MyConfirmation.vue";

let props = defineProps({
    origin: String,
    player: Object,
    deletable: Boolean,
});

let form = useForm({
    name: '',
});

let showConfirmation = ref(false);

onMounted(() => {
    if (props.player !== undefined) {
        form.name = props.player.name;
    }
});

let submit = () => {
    if (editMode.value) {
        form.put(`/players/${props.player.id}`);
    } else {
        form.post('/players');
    }
};

let deleteEntity = () => {
    showConfirmation.value = false;
    Inertia.delete(`/players/${props.player.id}`);
};

const editMode = computed(() => props.player !== undefined);
const title = computed(() => editMode.value ? "Spieler bearbeiten" : "Neuer Spieler");
const submitButtonText = computed(() => editMode.value ? "Speichern" : "Hinzufügen");

</script>

<template>
    <div>
        <Head title="Spieler" />

        <button
            tabindex="-1"
            class="hidden md:block fixed z-20 inset-0 h-full w-full bg-black opacity-50 cursor-default"
        ></button>
        <div
            class="relative z-30 w-full max-w-xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2">
            <div class="sm:px-2 lg:px-4 sm:py-2 lg:py-4">
                <div class="font-medium text-lg text-gray-900 ml-3 mb-4">{{ $t(title) }}</div>

                <div
                    class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg sm:px-2 lg:px-4 bg-white">
                    <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                        <div class="space-y-8 divide-y divide-gray-200 my-3 mx-2">
                            <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                <MyTextInput class="sm:col-span-6" v-model="form.name" :error="form.errors.name"
                                           id="name" :label="$t('Name')" autofocus
                                />
                            </div>
                            <div class="py-5">
                                <div class="flex justify-between">
                                    <MyButton theme="danger" v-if="deletable" @click="showConfirmation=true">
                                        Löschen
                                    </MyButton>
                                    <div class="w-full flex justify-end">
                                        <MyButton theme="abort" @click="Inertia.get(origin)">
                                            Abbrechen
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
    <MyConfirmation v-if="showConfirmation" @canceled="showConfirmation=false" @confirmed="deleteEntity">
        {{ `Spieler '${player.name}' löschen`}}
    </MyConfirmation>
</template>
