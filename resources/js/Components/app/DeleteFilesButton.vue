<template>
    <button @click="onDeleteClick()">
        Delete
    </button>
    
    <ConfirmationDialog :show="showDeleteDialog" message="Are you sure you want to delete selected files?" @cancel="onDeleteCancel" @confirm="onDeleteConfirm">

    </ConfirmationDialog>

</template>

<script setup>
import { ref } from 'vue';
import ConfirmationDialog from '../ConfirmationDialog.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { showErrorDialog, showSuccesNotification } from '@/event-bus';

const page = usePage();
const deleteFilesForm = useForm({
    all: null,
    ids: [],
    parent_id: null
})


const showDeleteDialog = ref(false);

const props = defineProps({
    deleteAll: {
        type: Boolean,
        required: false,
        default: false
    },
    deleteIds: {
        type: Array,
        required: false
    }
})

const emit = defineEmits(['delete'])





function onDeleteClick() {
   if(!props.deleteAll && !props.deleteIds.length) {
    showErrorDialog('Please select at least one file to delete')
    return
   } 
    showDeleteDialog.value = true;
} 

function onDeleteCancel() {
    showDeleteDialog.value = false;

}

function onDeleteConfirm() {
    deleteFilesForm.parent_id = page.props.folder.id
    if(props.deleteAll) {
        deleteFilesForm.all = true
    } else {
        deleteFilesForm.ids = props.deleteIds
    }
    deleteFilesForm.delete(route('file.delete'), {
        onSuccess: () => {
            showDeleteDialog.value = false
            emit('delete')
            showSuccesNotification('Selected files have been deleted')
        }
    })

}

</script>