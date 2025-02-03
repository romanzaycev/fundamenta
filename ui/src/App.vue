<template>
    <fnda-page :loading="store.isLoading">
        <router-view></router-view>
    </fnda-page>
    <fnda-notifications></fnda-notifications>
</template>

<style lang="scss">

</style>

<script lang="ts">
import {defineComponent} from "vue";
import FndaPage from "@/components/layout/FndaPage.vue";
import FndaNotifications from "@/components/ui/FndaNotifications.vue";
import {useAppStore} from "@/stores";

export default defineComponent({
    components: {
        FndaNotifications,
        FndaPage,
    },
    computed: {
        store() {
            return useAppStore();
        },
    },
    mounted() {
        this.store.load();
    },
});
</script>
