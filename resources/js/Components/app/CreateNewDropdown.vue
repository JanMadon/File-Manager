<template>
    <Menu as="div" class="relative w-full inline-block text-left border rounded-md px-4 py-2 text-sm font-medium text-gray-800 
bg-white hover:bg-gray-300 focus:outline-none focus-visible:ring-2 ">
        <MenuButton class="w-full text-center ">
            Create New
        </MenuButton>

        <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
            <MenuItems class="absolute left-0 mt- w-36 origin-top-right divide-y  divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none overflow-hidden">
                <div class="px-1 py-1 hover:bg-gray-300">
                    <MenuItem v-slot="{ active }">
                    <a href="#" @click.prevent="showCreateFolderModel"
                        class="text-gray-700 block px-4 py-2 font-bold">
                        New Folder
                    </a>
                    </MenuItem>
                </div>

                <div >
                    <FileUploadMenuItem class=" hover:bg-gray-300 "/>
                    <FolderUploadMenuItem class="cursor-pointer hover:bg-gray-300"/> 
                </div>

            </MenuItems>
        </transition>
    </Menu>
    <CreateFolderModel v-model="createFolderModel"/>
</template>

<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import CreateFolderModel from '@/Components/app/CreateFolderModel.vue';
import FileUploadMenuItem from '@/Components/app/FileUploadMenuItem.vue';
import FolderUploadMenuItem from '@/Components/app/FolderUploadMenuItem.vue';
import { ref } from 'vue';

const createFolderModel = ref(false)

function showCreateFolderModel() {
    createFolderModel.value = true
}
</script>


<style scoped>

</style>