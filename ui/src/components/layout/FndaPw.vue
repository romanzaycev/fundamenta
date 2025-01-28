<template>
    <div :class="bem()">
        <div :class="bem('i')">
            <slot></slot>
        </div>
    </div>
</template>

<style lang="scss">
.fnda-pw {
    padding-left: 8px;
    padding-right: 8px;

    &__i {
        margin: 0 auto;
        max-width: 1200px;
    }
}
</style>

<script lang="ts">
import {defineComponent} from "vue";

export default defineComponent({
    name: "fnda-pw",
    components: {},
});
</script>
