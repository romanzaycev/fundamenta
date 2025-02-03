<template>
    <div :class="bem(null, {loading})">
        <slot></slot>
    </div>
</template>

<style lang="scss">
.fnda-page {
    opacity: 0;
    transition: opacity 0.3s;

    &_loading {
        opacity: 0.3;
    }
}
</style>

<script lang="ts">
import {defineComponent} from "vue";

export default defineComponent({
    name: "fnda-page",
    props: {
        loading: {
            type: Boolean,
            default: false,
        },
    },
    components: {},
});
</script>
