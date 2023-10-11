<template>
    <button @click="onClick"
              class="inline-flex mr-2 items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
</svg>


          Delete Forever
        
      </button>
  
      <ConfirmationDialog :show="showConfirmationDialog"
                          message="Are you sure you want to Delte files?"
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
  
  const emit = defineEmits(['delete'])
  
  function onClick(){
  
      if(!props.allSelected && !props.selectedIds.length ){
          showErrorDialog('Please select files to delete');
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
  
      form.delete(route('file.deleteForever'),{
          onSuccess: ()=> {
              showConfirmationDialog.value = false;
              emit('delete');
              showSuccessNotification('Files delete successfully')
            
          }
          })

  }
  </script>