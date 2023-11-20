<template>
    <Model :show="show" max-width="md">
        <div class="p-6 ">
            <h2 class="text-2xl mb-2 text-red-600 font-semibold ">Error</h2>
            {{ message }}
            <div class="mt-6 flex justify-end">
                <PrimaryButton @click="close"> OK </PrimaryButton>
            </div>
        </div>
    </Model>
</template>

<script setup>
import Model from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { SHOW_ERROR_DIALOG, emitter } from '@/event-bus';
import {ref, onMounted} from 'vue';
//import { FILE_UPLOAD_STARTED, emitter, showErrorDialog } from '@/event-bus';


const show = ref(false);
const message = ref('');

const emit = defineEmits(['close']);

function close() {
    show.value = false;
    message.value = '';
}

onMounted(() => {
    emitter.on(SHOW_ERROR_DIALOG, ({message: msg}) => {
        show.value = true
        message.value = msg
    })
})

</script>