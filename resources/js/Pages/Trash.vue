<template>
    <AuthenticatedLayout>
    
        <nav class="flex items-center justify-end p-1 mb-3">

      
            <div>

                <DeleteForever :all-selected="allSelected" :selected-ids="selectedIds" @delete="onDelete"/>
<RestoreFilesButton :all-selected="allSelected" :selected-ids="selectedIds"  @restore="onDelete"/>
               
            </div>
           
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
                    Path    
                </th>
              
           
            </tr>
            </thead>
                <tbody>
                    <tr v-for="file of allFiles.data" :key="file.id" 
                    @click=" $event => toggleFileSelect(file)"
                    
                    class=" border-b cursor-pointer transition duration-300 ease-in-out hover:bg-gray-100 cursor-pointer" :class="(selected[file.id] || allSelected) ? 'bg-blue-50' : 'bg-white'">

                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-[30px] max-w-[30px] pr-0 ">
                        <Checkbox @change="$event => onSelectCheckboxChange(file)" v-model="selected[file.id]" :checked="selected[file.id] || allSelected"/>
                        </td> 
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex  items-center">
                        <FileIcon :file="file"/>
                        {{ file.name }}
                        </td>   
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900  items-center">
                        {{ file.path }}
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
import DownloadFilesButton from '@/Components/app/DownloadFilesButton.vue';
import RestoreFilesButton from '@/Components/app/RestoreFilesButton.vue';
import DeleteForever from '@/Components/app/DeleteForever.vue';


const allSelected = ref(false);
const selected = ref({});
const loadMoreIntersect = ref(null);

const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next
})

const selectedIds = computed(() => Object.entries(selected.value).filter( a => a[1]).map( a=> a[0]))




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
    selected.value = {}
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