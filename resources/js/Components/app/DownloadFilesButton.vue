<template>
    <PrimaryButton @click="download">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"      stroke="currentColor"            class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" />
        </svg>
        Download
    </PrimaryButton>
</template>

<script setup>
import ConfirmationDialog from '../ConfirmationDialog.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '../PrimaryButton.vue';
import { httpGet } from '@/Helper/http-helper';

const page = usePage();




const props = defineProps({
    all: {
        type: Boolean,
        required: false,
        default: false
    },
    ids: {
        type: Array,
        required: false
    },
    sharedWithMe: false,
    sharedByMe: false,
})

function download() {
    if(!props.all && props.ids.length == 0){
        return;
    }

    const p = new URLSearchParams();
    if(page.props.folder?.id){
        p.append('parent_id', page.props.folder?.id)
    }

    if(props.all){
        p.append('all', props.all ? 1 : 0);
    } else {
        for(let id of props.ids){
            p.append('ids[]', id);
        }
    }

    let url = route('file.download');

    if(props.sharedWithMe){
        url = route('file.downloadSharedWithMe')
    } else if (props.sharedByMe) {
        url = route('file.downloadSharedByMe')
    }

    httpGet(url +'?'+p.toString())
        .then(res => {
            if (!res.url) return

            const a = document.createElement('a');
            a.download = res.filename;
            a.href = res.url;
            a.click();
        })
}


</script>