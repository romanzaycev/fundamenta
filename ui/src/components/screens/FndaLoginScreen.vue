<template>
    <div :class="bem()">
        <FndaPw>
            <div class="fnda-logo" :class="bem('logo')"></div>
            <VaInnerLoading :loading="store.isLoading">
                <VaForm
                    ref="login-form"
                    class="flex flex-col gap-2 mb-2"
                    @submit.prevent="handleSubmit"
                >
                    <div :class="bem('row')">
                        <VaInput
                            v-model="form.login"
                            label="Login"
                            name="Login"
                            :rules="[(v) => Boolean(v) || 'Login is required']"
                        />
                    </div>

                    <div :class="bem('row')">
                        <VaInput
                            v-model="form.password"
                            label="Password"
                            type="password"
                            name="Password"
                            :rules="[
                                (v) => Boolean(v) || 'Password is required',
                                (v) => v.length > 6 || 'Password must be a least 6 symbols',
                            ]"
                        />
                    </div>

                    <div
                        :class="bem('row')">
                        <VaButton
                            :disabled="!isFormValid() || store.isLoading"
                            type="submit"
                        >Login</VaButton>
                    </div>
                </VaForm>
            </VaInnerLoading>
        </FndaPw>
    </div>
</template>

<style lang="scss">
.fnda-login-screen {
    height: 100vh;
    display: grid;
    place-items: center;

    &__row {
        margin: 20px 0;
    }

    &__logo {
        margin: 40px auto;
    }
}
</style>

<script lang="ts">
import {defineComponent, reactive, ref} from "vue";
import {useForm, VaForm} from "vuestic-ui";
import FndaPw from "@/components/layout/FndaPw.vue";
import {useLoginStore} from "@/stores/login";
import {authHolder} from "@/services";

export default defineComponent({
    name: "fnda-login-screen",
    components: {
        FndaPw,
        VaForm,
    },
    mounted() {
        if (authHolder.hasToken()) {
            this.$router.push({
                path: '/',
                replace: true
            });
        }
    },
    computed: {
        store() {
            return useLoginStore();
        }
    },
    methods: {
        isFormValid() {
            const keys = Object.keys(this.errorMessagesNamed);

            for (let i = 0; i < keys.length; i++) {
                if (this.errorMessagesNamed[keys[i]].length > 0) {
                    return false;
                }
            }

            return true;
        },
        handleSubmit() {
            if (!this.store.isLoading && this.isFormValid()) {
                this.store.make(
                    this.form.login,
                    this.form.password,
                );
            }
        },
    },
    setup(props, { emit, expose }) {
        const form = reactive({
            login: "",
            password: "",
        });
        const {errorMessagesNamed} = useForm("login-form");

        return {form, errorMessagesNamed};
    }
});
</script>
