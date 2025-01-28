import {Api, FetchOptions} from "@/services/api";
import {container} from "tsyringe";

export class ProxiedApi extends Api {
    async fetch(endpoint: string, options: FetchOptions = {}): Promise<any> {
        const realApi = container.resolve<Api>('realApi');

        endpoint = this.normalizeEndpoint(endpoint);

        switch (endpoint) {
            /*
            case '/login':
                return new Response(JSON.stringify({
                    is_success: false,
                    message: "kek",
                    is_2fa_needed: false,
                }));*/

            default:
                return realApi.fetch(endpoint, options);
        }
    }
}
