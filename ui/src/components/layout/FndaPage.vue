<template>
    <div :class="bem()">
        <slot></slot>
    </div>
</template>

<style lang="scss">
.fnda-page {

}
</style>

<script lang="ts">
import {defineComponent} from "vue";

export default defineComponent({
    name: "fnda-page",
    components: {},
});
</script>
