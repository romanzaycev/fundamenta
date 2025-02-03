import {defineStore} from "pinia";
import {Api, authHolder} from "@/services";
import {container} from "tsyringe";
import {HttpMethod} from "@/services/api";
import {useNotificationStore} from "@/stores/notification";

let api: Api|null = null;

const getApi = (): Api => {
    if (api) {
        return api;
    }

    api = container.resolve<Api>(Api.name);

    return api;
}

export const useAppStore = defineStore('app', {
    state: () => ({
        isLoading: false,
        initialized: false,
    }),
    actions: {
        async load() {
            if (!this.isLoading) {
                this.isLoading = true;

                if (!authHolder.hasToken()) {
                    this.isLoading = false;
                    this.$router.push({
                        path: '/login/',
                        replace: true,
                    });
                    return;
                }

                try {
                    const a = getApi();
                    const resp = await a.fetch(
                        'is-alive',
                        {
                            method: HttpMethod.GET,
                        }
                    );
                    const data = await a.unwrapToApiResult(resp);

                    if (!data.is_success) {
                        this.isLoading = false;
                        useNotificationStore().showError("Application", data.message ?? "Error");
                        return;
                    }

                    this.initialized = true;
                } catch (e) {
                    this.isLoading = false;
                    useNotificationStore().showError("Application", e.message ?? "Error");
                }
            }
        },
    },
});
