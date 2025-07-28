<template>
    <div>
        <Navbar/>
        <main>
            <router-view></router-view> 
        </main>
    </div>
</template>

<script setup>
    import Navbar from './components/layout/Navbar.vue'
</script>

<style>
nav {
    padding: 0.6em;
    background-color: #eee;
}
nav a {
    margin-right: 0.6em;
}
</style>