<template>
    <button @click="onClick()" class="border border-red-800 mr-3">Delete forever
    {{ selectedIds }}
    </button>
    
    <ConfirmationDialog :show="showConfirmationDialog" message="Are you sure you want to delete selected files?" @cancel="onCancel" @confirm="onConfirm">

    </ConfirmationDialog>

</template>

<script setup>
import { ref } from 'vue';
import ConfirmationDialog from '../ConfirmationDialog.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { showErrorDialog, showSuccesNotification } from '@/event-bus';

const page = usePage();
const form = useForm({
    all: null,
    ids: [],
    parent_id: null
})


const showConfirmationDialog = ref(false);

const props = defineProps({
    allSelected: {
        type: Boolean,
        required: false,
        default: false
    },
    selectedIds: {
        type: Array,
        required: false
    }
})

const emit = defineEmits(['delete'])

function onClick() {
   if(!props.allSelected && !props.selectedIds.length) {
    showErrorDialog('Please select at least one file to delete')
    return
   } 
     showConfirmationDialog.value = true;
} 

function onCancel() {
    showConfirmationDialog.value = false;

}

function onConfirm() {
    if(props.allSelected) {
        form.all = true
        form.ids = []
    } else {
        form.ids = props.selectedIds
    }
    form.delete(route('file.deleteForever'), {
        onSuccess: () => {
            showConfirmationDialog.value = false
            emit('delete')
            showSuccesNotification('Selected files have been deleted forever')
        }
    })

}

</script>