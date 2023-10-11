<template>
    <button @click="onClick"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2" >
  <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
</svg>

          Restore
        
      </button>
  
      <ConfirmationDialog :show="showConfirmationDialog"
                          message="Are you sure you want to delete files?"
                          @cancel="onCancel"
                          @confirm="onConfirm">
      </ConfirmationDialog>
  
  </template>
  
  <script setup>
  import ConfirmationDialog from './ConfirmationDialog.vue';
  import {ref} from 'vue';
  import { useForm, usePage } from '@inertiajs/vue3';
  import {showErrorDialog, showSuccessNotification} from "@/event-bus.js";
  
  const page = usePage();
  
  const form = useForm({
      all: null,
      ids: [],
      parent_id : null,
  })
  
  const showConfirmationDialog = ref(false);
  const props = defineProps({
      allSelected: {
          type: Boolean,
          default: false,
          required: false,
      },
      selectedIds : {
          type: Array,
          required: false,
      }
  })
  
  const emit = defineEmits(['restore'])
  
  function onClick(){
  
      if(!props.allSelected && !props.selectedIds.length ){
          showErrorDialog('Please select files to restore');
          return;
      }
      showConfirmationDialog.value = true;
      
      console.log('delete files', props.selectedIds, props.allSelected);
  }
  
  function onCancel(){
      showConfirmationDialog.value = false;
  }
  
  function onConfirm(){
  
     
      if(props.allSelected){
          form.all = true;
      }else{
          form.ids = props.selectedIds;
      }
  
      form.post(route('file.restore'),{
          onSuccess: ()=> {
              showConfirmationDialog.value = false;
              emit('restore');
              showSuccessNotification('Files restore successfully')
          }
          })

  }
  </script>