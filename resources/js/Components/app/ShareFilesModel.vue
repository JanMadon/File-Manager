<template>
    <modal :show="props.modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg" font-mediom text-gray-900>
                Share Files
            </h2>
            <div class="mt-6">
                <InputLabel for="shareEmail" value="Enter Email Adress" class="sr-only"/>

                <TextInput  type="text" 
                            ref="emailInput"
                            id="shareEmail" 
                            v-model="form.email"
                            :class="form.errors.email ? 'border-red-500 focus:bolder-red-500 focus:ring-red-500' : ''"
                            class="mt-1 block w-full"
                            placeholder="Enter Email Address"
                            @kayup.enter="share"
                />
                <InputError :message="form.errors.email" class="mt-2" />

            </div>
            <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModel">Cancel</SecondaryButton>
                    <PrimaryButton  class="ml-3" :class="{'opacity-25' : form.processing}" 
                                    @click="share" :disabled="form.processing">
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
import { showSuccesNotification } from '@/event-bus';

const form = useForm({
    email: null,
    all: false,
    ids: [],
    parent_id: null
})

const page = usePage();
const emailInput = ref(null);

const props = defineProps({
    modelValue: Boolean,
    allSelected: Boolean,
    selectedIds: Array
})

const emit = defineEmits(['update:modelValue'])

function share() {
    const email = form.email
    form.parent_id = page.props.folder.id
    if(props.allSelected){
        form.all = true
        form.ids = []
    } else {
        form.ids = props.selectedIds
    }
    form.post(route('file.share'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModel()
            // show success notefication
            showSuccesNotification(`Selected files will be shared to "${email}" if the emails exists in the system`)
            form.reset();

        },
        onError: () => emailInput.value.focus()
    })
}

function closeModel(){
    emit('update:modelValue')
    form.clearErrors()
    form.reset()
}

function onShow() {
    nextTick(()=>emailInput.value.focus())
    
}



</script>