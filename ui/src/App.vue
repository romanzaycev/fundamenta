<template>
    <fnda-page>
        <router-view></router-view>
    </fnda-page>
</template>

<style lang="scss">

</style>

<script lang="ts">
import { defineComponent } from "vue";
import FndaPage from "@/components/layout/FndaPage.vue";

export default defineComponent({
    components: {
        FndaPage,
    }
});
</script>
