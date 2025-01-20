import {Api, ApiResponse, FetchOptions} from "@/services/api";
import {container, singleton} from "tsyringe";
import {delay} from "@/helpers/common";

@singleton()
export class ProxiedApi extends Api {
    async fetch<T extends ApiResponse | ArrayBuffer>(endpoint: string, options: FetchOptions = {}): Promise<T> {
        const realApi = container.resolve<Api>('realApi');

        endpoint = this.normalizeEndpoint(endpoint);

        switch (endpoint) {
            case '/state':
                await delay(800);
                // @ts-ignore
                return this.transformJson({
                    is_success: true,
                    data: {
                        goldCount: 1924588,
                        isWalletConnected: true,
                        walletAddr: 'UQBlcWJCn1ERR4cjM_iWbOyChcKoszSMjYHJnyII1BdJ63N5',
                        isFarmingFinished: true,
                        farmingTimeEnd: new Date().getTime() / 1000 + 14901,
                        farmingStartedAt: new Date().getTime() / 1000 - 100,
                        farmingGoldCountEnd: 120,
                        farmed: 334,
                    },
                });

            default:
                return realApi.fetch(endpoint, options);
        }
    }
}
