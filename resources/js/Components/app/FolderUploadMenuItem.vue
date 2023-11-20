<template>
    <MenuItem v-slot="{ active }">
    <a href="#" class="text-gray-700 block px-4 py-2 text-sm relative">
        Upload Forder
        <input @change="onChange" type="file" class="absolute left-0 top-0 bottom-0     right-0 cutsor-pointer opacity-0" multiple directory webkitdirectory >
    </a>
    </MenuItem>
</template>

<script setup>
import { MenuItem } from '@headlessui/vue'
import { FILE_UPLOAD_STARTED, emitter } from '@/event-bus';



//Methods
function onChange(ev) {
    emitter.emit(FILE_UPLOAD_STARTED, ev.target.files);
}

</script>