
<template>
    <div class="h-screen bg-gray-50 flex w-full gap-4">
        <Navigation />

        <main @drop.prevent="hendleDrop" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
            class="flex flex-col flex-1 px-4 overflow-hidden" :class="dragOver ? 'dropzone' : ''">

            <template v-if="dragOver" class="text-gray-500 text-center py-8">
                Drop files here to upload
            </template>
            <template v-else>
                <div class="flex items-center justify-between w-full">
                    <SearchForm />
                    <UserSettingsDropdown />
                </div>
                {{ fileUploadForm.progress }}
                <div class="flex-1 flex flex-col overflow-hidden">
                    <slot />
                </div>
            </template>

        </main>
    </div>
    <ErrorDialog />
    <FormProgress :form="fileUploadForm" />
    <Notification />
</template>

<script setup>
import Navigation from '@/Components/app/Navigation.vue';
import SearchForm from '@/Components/app/SearchFrom.vue';
import FormProgress from '@/Components/app/FormProgress.vue';
import UserSettingsDropdown from '@/Components/app/UserSettingsDropdown.vue';
import { FILE_UPLOAD_STARTED, emitter, showErrorDialog, showSuccesNotification } from '@/event-bus';
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import ErrorDialog from '@/Components/ErrorDialog.vue';
import Notification from '@/Components/Notification.vue';

//Uses
const page = usePage();
const fileUploadForm = useForm({
    files: [],
    relative_paths: [],
    parent_id: null
})

//Refs 
const dragOver = ref(false);

//methodes
function hendleDrop(ev) {
    dragOver.value = false
    const files = ev.dataTransfer.files

    if (!files.length) {
        return
    }

    uploadFiles(files)

}

function onDragOver() {
    dragOver.value = true
}

function onDragLeave() {
    dragOver.value = false
}

function uploadFiles(files) {

    fileUploadForm.parent_id = page.props.folder.id;
    fileUploadForm.files = files;
    fileUploadForm.relative_paths = [...files].map(f => f.webkitRelativePath)

    fileUploadForm.post(route('file.store'), {
        onSuccess: () => {
            showSuccesNotification(`${files.length} files have uploaded`)
        },
        onError: errors => {
            let message = '';

            if (Object.keys(errors).length > 0) {
                message = errors[Object.keys(errors)[0]]
            } else {
                message = 'Error durning file upload. Please try again later';
            }

            showErrorDialog(message)
        },
        onFinish: () => {
            fileUploadForm.clearErrors();
            fileUploadForm.reset();
        }



    });


}



//hooks
onMounted(() => {
    emitter.on(FILE_UPLOAD_STARTED, uploadFiles);
})
</script>

<style scoped>
.dropzone {
    width: 100%;
    height: 100%;
    color: #8d8d8d;
    border: 2px dashed gray;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
