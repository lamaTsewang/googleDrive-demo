<template>
<nav class="min-w-[260px]">
    
    <Link :href="route('myFiles')" 
        class="h-[80px] px-3 flex items-center gap-3">
            <ApplicationLogo
            class="block h-9 w-auto f
            ill-current text-gray-800"
            />
       
        Tsewang Storage
    </Link>
  
    <div class="px-3">
        <CreateNewDropDown />

        <div class="py-3">
            <NavLink :href="route('myFiles')" :active="$page.url == '/my-files'">My Files</NavLink>
            <NavLink href="/">Shared with me</NavLink>
            <NavLink href="/">Shared by me</NavLink>
            <NavLink :href="route('trash')" :active="$page.url == '/trash'">Trash</NavLink>
        </div>
    </div>
  
</nav>
</template>
<script setup>
import { Link } from '@inertiajs/vue3'
import NavLink from '@/Components/NavLink.vue';
import CreateNewDropDown from '@/Components/app/CreateNewDropDown.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

</script>