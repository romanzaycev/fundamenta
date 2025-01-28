import {Api, HttpMethod} from "@/services/api";
import authHolder from "@/services/auth-holder";

interface LoginResult {
    isSuccess: boolean,
    message?: string,
    is2faNeeded?: boolean,
}

export class Login {
    constructor(private readonly api: Api) {}

    public async make(
        login: string,
        password: string,
        answer2fa?: string | null,
    ): Promise<LoginResult> {
        try {
            let resp = await this
                .api
                .fetch('login', {
                    method: HttpMethod.POST,
                    data: {
                        login,
                        password,
                        answer2fa,
                    },
                });

            const data = await this.api.unwrapToApiResult(resp);

            if (!data.is_success) {
                return {
                    isSuccess: false,
                    message: data.message
                        ? data.message
                        : 'Login error',
                };
            }

            const tData = data.data;

            if (!tData.is_2fa_needed) {
                authHolder.updateByLoginResponse(tData);
            }

            return Promise.resolve({
                isSuccess: true,
                is2faNeeded: tData.is_2fa_needed,
            });
        } catch (e) {
            return Promise.resolve({
                isSuccess: false,
                message: e.message ? e.message : "API error",
            });
        }
    }
}
