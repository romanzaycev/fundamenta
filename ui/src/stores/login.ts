import {defineStore} from "pinia";
import {Login} from "@/services";
import {container} from "tsyringe";
import {useNotificationStore} from "@/stores/notification";

interface LoginStoreState {
    isLoading: boolean,
    is2faNeeded: boolean,
}

let service: Login|null = null;

export const useLoginStore = defineStore('login', {
    state: () => ({
        isLoading: false,
        is2faNeeded: false,
    }) as LoginStoreState,
    actions: {
        make(login: string, password: string, answer2fa: string|null = null) {
            if (!this.isLoading) {
                if (!service) {
                    service = container.resolve<Login>(Login.name);
                }

                this.isLoading = true;
                service
                    .make(
                        login,
                        password,
                        answer2fa,
                    )
                    .then((result) => {
                        if (!result.isSuccess) {
                            this.isLoading = false;
                            useNotificationStore().showError("Auth failed", result.message);
                        } else {
                            if (result.is2faNeeded) {
                                this.is2faNeeded = result.is2faNeeded;
                                this.isLoading = false;
                                return;
                            }

                            useNotificationStore().showSuccess("Auth", "Successful");
                            this.$router.push({
                                path: '/',
                                replace: true
                            });
                        }
                    })
                    .catch((e) => {
                        this.isLoading = false;
                        useNotificationStore().showError("Auth error", e.message ? e.message : e);
                    });
            }
        },
    }
});
