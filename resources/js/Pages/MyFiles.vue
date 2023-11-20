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
                        <Link :href="route('myFiles', {folder: ans.path})">
                            {{ans.name}}
                        </Link>
                    </div>
                    
                </li>
            </ol>
        </nav>
       <table  class="min-w-full">
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
                <tr v-for="file of files.data" :key="file.id" @dblclick="openFolder(file)" class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100 cursor-pointer">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                        <FileIcon :file="file"/>
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
      

       <div v-if="!files.data.length" class="py-8 text-center text-sm text-gray-400">
            There is no data in this folder
       </div>

    </AuthenticatedLayout>

</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {router, Link} from '@inertiajs/vue3';
import FileIcon from '@/Components/app/FileIcon.vue'

function openFolder(file){
    if(!file.id_folder){
        return;
    } 

    router.visit(route('myFiles', {folder: file.path}));
}

const {files} = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object
})

</script>

