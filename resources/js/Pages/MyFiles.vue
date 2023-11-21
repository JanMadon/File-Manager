<template>
    <AuthenticatedLayout>
        <nav class="flex items-center justify-between p-1 bm-3">
            <ol class="inline-flex items-center space-x-1 md:space-x-1 ">
                <li v-for="ans of ancestors.data" :key="ans.id" class="inline-flex items-center">
                    <!-- {{ ans }} -->

                    <Link v-if="!ans.parent_id" :href="route('myFiles')">

                    My Files

                    </Link>
                    <div v-else class="flex items-center">
                        >
                        <Link :href="route('myFiles', { folder: ans.path })">
                        {{ ans.name }}
                        </Link>
                    </div>

                </li>
            </ol>
        </nav>

        <div class="flex-1 overflow-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            Name
                        </th>
                        <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            Owner
                        </th>
                        <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            Last Modified
                        </th>
                        <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            Size
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="file of allFiles.data" :key="file.id" @dblclick="openFolder(file)"
                        class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100 cursor-pointer">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                            <FileIcon :file="file" />
                            {{ file.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 ">
                            {{ file.owner }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 ">
                            {{ file.updated_at }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 ">
                            {{ file.size }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="!allFiles.data.length" class="py-8 text-center text-sm text-gray-400">
                There is no data in this folder
            </div>
            <div ref="loadMoreIntersect"></div>
        </div>




    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import FileIcon from '@/Components/app/FileIcon.vue'
import {ref, onMounted, onUpdated} from 'vue'
import {httpGet} from '@/Helper/http-helper';

const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object
})

const loadMoreIntersect = ref(null);
const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next
})



function openFolder(file) {
    if (!file.id_folder) {
        return;
    }

    router.visit(route('myFiles', { folder: file.path }));
}

function loadMore(){
    console.log('load more')
    console.log(allFiles.value.next)

    if(allFiles.value.next == null){
        return
    }

    httpGet(allFiles.value.next)
        .then(res => {
            allFiles.value.data = [...allFiles.value.data, ...res.data],
            allFiles.value.next = res.links.next
        })
}

onUpdated(() => {
    console.log('qqq')
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next
    }
}),

onMounted(() => {
    const observer = new IntersectionObserver((entries) => entries.forEach(entry => entry.isIntersecting && loadMore()),
        {
            rootMargin: '-250px 0px 0px 0px'
        })

    observer.observe(loadMoreIntersect.value);
})



</script>

