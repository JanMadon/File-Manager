<template>
    <modal :show="modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg" font-mediom text-gray-900>
                Create New Folder
            </h2>
            <div class="mt-6">
                <InputLabel for="folderName" value="Folder Name" class="sr-only"/>

                <TextInput  type="text" 
                            ref="folderNameInput"
                            id="folderName" 
                            v-model="form.name" 
                            class="mt-1 block w-full"
                            :class="form.errors.name ? 'border-red-500 focus:bolder-red-500 focus:ring-red-500' : ''"
                            placeholder="Folder Name"
                            @kayup.enter="createForder"
                />
                
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModel">Cancel</SecondaryButton>
                    <PrimaryButton  class="ml-3" :class="{'opacity-25' : form.processing}" 
                                    @click="createForder" :disabled="form.processing">
                        Submit
                    </PrimaryButton>
            </div>
            
        </div>
    </modal>
</template>


<script setup>
import modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue'; 
import InputError from '@/Components/InputError.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, nextTick } from 'vue';
import { showErrorDialog, showSuccesNotification } from '@/event-bus';


const form = useForm({
    name: '',
    parent_id: null,
})

const page = usePage();
const folderNameInput = ref(null);

// function createForder() {

// form.parent_id = page.props.folder.id
// const name = form.name;
// form.post(route('folder.create'), {
//     preserveScroll: true,
//     onSuccess: () => {
//         closeModal()
//         // Show success notification
//         //showSuccessNotification(`The folder "${name}" was created`)
//         form.reset();
//     },
//     onError: () => folderNameInput.value.focus()
// })
// }

function createForder() {
    form.parent_id = page.props.folder.id
    const name = form.name
    form.post(route('folder.create'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModel()
            // show success notefication
            showSuccesNotification(`The folder "${name}" was created`)
            form.reset();

        },
        onError: () => folderNameInput.value.focus()
    })
}

function closeModel(){
    emit('update:modelValue')
    form.clearErrors()
    form.reset()
}

function onShow() {
    nextTick(()=>folderNameInput.value.focus())
    
}

const {modelValue} = defineProps({
    modelValue: Boolean
})

const emit = defineEmits(['update:modelValue'])

</script>