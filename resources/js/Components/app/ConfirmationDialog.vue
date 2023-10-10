<template>

<Modal :show="show" max-width="md">
    <div class="p-6">

        <h2 class="text-2xl mb-2 font-semibold">Please Confirm</h2>
        

        <div class="mt-6 flex justify-end">
            <SecondaryButton @click="$event => emit('cancel')">Cancel</SecondaryButton>

            <DangerButton class="ml-3" @click="$event => emit('confirm')">Confirm</DangerButton>
        </div>

    </div>


</Modal>

</template>

<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';






const emit = defineEmits(['cancel', 'confirm'])

const props = defineProps({
    show: {
        type: Boolean,
     
    },
    message: {
        type: String,
      
    }
})

</script>