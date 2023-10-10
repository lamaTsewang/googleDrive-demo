<template>
    <AuthenticatedLayout>
        <!-- <pre>{{ selected }}</pre>
        <pre>{{ allSelected }}</pre> -->
        <nav class="flex items-center justify-between p-1 mb-3">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li v-for="ans of ancestors.data" :key="ans.id" class="inline-flex ietms-center">
                   <Link v-if="!ans.parent_id" :href="route('myFiles')" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                  <HomeIcon class="w-4 h-4"/>
                    <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                  clip-rule="evenodd"></path>
                        </svg>
                My Files
                </Link>
                <div v-else class="flex items-center">
                    <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                  clip-rule="evenodd"></path>
                        </svg>
                        <Link :href="route('myFiles', {folder: ans.path})"
                              class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">
                            {{ ans.name }}
                        </Link> 
                </div>
                </li>
            </ol>

            <div class="">

                <DeleteFilesButton :delete-all="allSelected" :delete-ids="selectedIds" @delete="onDelete"/>
               
            </div>
<!-- <pre>{{ selectedIds }}</pre> -->
        </nav>
        <div class="flex-1 overflow-auto">
            <table class="min-w-full">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left w-[30px] max-w-[30px] pr-0">
                   <Checkbox @change="onSelectedAllChange" v-model:checked="allSelected"/>
                </th>
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
                    <tr v-for="file of allFiles.data" :key="file.id" 
                    @click=" $event => toggleFileSelect(file)"
                    @dblclick="openFolder(file)"
                    class=" border-b cursor-pointer transition duration-300 ease-in-out hover:bg-gray-100 cursor-pointer" :class="(selected[file.id] || allSelected) ? 'bg-blue-50' : 'bg-white'">

                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-[30px] max-w-[30px] pr-0 ">
                        <Checkbox @change="$event => onSelectCheckboxChange(file)" v-model="selected[file.id]" :checked="selected[file.id] || allSelected"/>
                        </td> 
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex  items-center">
                        <FileIcon :file="file"/>
                        {{ file.name }}
                        </td>   
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900  items-center">
                        {{ file.owner }}
                        </td>   
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900  items-center">
                        {{ file.updated_at }}
                        </td>   
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900  items-center">
                        {{ file.size }}
                        </td>   
                    </tr>
                </tbody>
          
        

       </table>
       
     
      
       <div v-if="!allFiles.data.length" class="py-8 text-center text-sm text-gray-400">
        There is no data in this folder
       </div>

       <div ref="loadMoreIntersect">

       </div>
    </div>


    </AuthenticatedLayout>
  
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router , Link} from '@inertiajs/vue3';
import {HomeIcon} from '@heroicons/vue/20/solid'
import FileIcon from '@/Components/app/FileIcon.vue';
import { onMounted } from 'vue';
import { onUpdated } from 'vue';
import { ref , computed} from 'vue';
import {httpGet} from "@/Helper/http-helper.js";
import {all} from "axios";
import Checkbox from '@/Components/Checkbox.vue';
import DeleteFilesButton from '@/Components/app/DeleteFilesButton.vue';




const allSelected = ref(false);
const selected = ref({});
const loadMoreIntersect = ref(null);

const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next
})

const selectedIds = computed(() => Object.entries(selected.value).filter( a => a[1]).map( a=> a[0]))



function openFolder(file) {
    if (!file.is_folder) {
        return;
    }

    router.visit(route('myFiles', {folder: file.path}))
}
const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
})

function loadMore(){
    console.log('load more');

    console.log(allFiles.value.next);

    if (!allFiles.value.next) {
        return;
    }
    httpGet(allFiles.value.next)
        .then(res => {
            allFiles.value.data = [...allFiles.value.data, ...res.data]
            allFiles.value.next = res.links.next;
        
        }) 
        
}


function onDelete() {
    allSelected.value = false
    selected.value = null
}
function onSelectedAllChange(){
   allFiles.value.data.forEach(f => {
    selected.value[f.id] = allSelected.value;
   })
}

function toggleFileSelect(file){
   

    selected.value[file.id] = !selected.value[file.id];

    onSelectCheckboxChange(file);
}
function onSelectCheckboxChange(file){
   
   if(!selected.value[file.id]){
       allSelected.value = false;
   }
   else{
       allSelected.value = allFiles.value.data.every(f => selected.value[f.id]);
     
   }
   
}
onUpdated(() => {
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next
    }
})

onMounted(() => {
    const observer = new IntersectionObserver((entries) => entries.forEach(entry => entry.isIntersecting && loadMore()), {
        rootMargin: '-250px 0px 0px 0px'
    })

    observer.observe(loadMoreIntersect.value)

})



</script>