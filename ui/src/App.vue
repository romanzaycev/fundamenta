<template>
    <fnda-page>
        <router-view></router-view>
    </fnda-page>
    <fnda-notifications></fnda-notifications>
</template>

<style lang="scss">

</style>

<script lang="ts">
import { defineComponent } from "vue";
import FndaPage from "@/components/layout/FndaPage.vue";
import FndaNotifications from "@/components/ui/FndaNotifications.vue";

export default defineComponent({
    components: {
        FndaNotifications,
        FndaPage,
    }
});
</script>
