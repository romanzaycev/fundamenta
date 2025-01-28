<template>

</template>

<script lang="ts">
import {defineComponent} from "vue";
import {FndaStoreMutation} from "@/types";
import {useNotificationStore} from "@/stores";

export default defineComponent({
    name: "fnda-notifications",
    created() {
        const store = useNotificationStore();

        store.$subscribe((mutation: FndaStoreMutation & any, state) => {

            if (mutation.storeId === "notification" && mutation.events.type === 'set') {
                const ev = mutation.events;
                const hop = Object.prototype.hasOwnProperty;

                if (hop.call(ev.newValue, ['title']) && hop.call(ev.newValue, ['message'])) {
                    let color: string | null = null;
                    let icon: string | null = null;

                    switch (ev.key) {
                        case "error":
                            color = "danger";
                            icon = "warning";
                            break;

                        case "success":
                            color = "success";
                            icon = "check_circle";
                            break;

                        case "text":
                            color = "primary";
                            icon = "info";
                            break;
                    }

                    if (color && icon) {
                        this.$vaToast.init({
                            title: ev.newValue.title,
                            message: ev.newValue.message,
                            color,
                            position: "top-center",
                            iconClass: icon,
                        });
                    }
                }
            }
        });
    },
});
</script>
