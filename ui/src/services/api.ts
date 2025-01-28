import {AuthHolder} from "./auth-holder";

export enum HttpMethod {
    GET = "GET",
    POST = "POST",
    PUT = "PUT",
    PATCH = "PATCH",
    DELETE = "DELETE",
}

export interface ApiResponse {
    isSuccess: boolean,
    message: string | null,
    data?: any,
}

export interface FetchOptions {
    method?: HttpMethod,
    headers?: Map<string, string>,
    data?: string | {},
}

export interface ValidationError {
    field: string,
    error: string,
}

export class Api {
    constructor(
        private readonly baseUri: string,
        private readonly authHolder: AuthHolder,
    ) {}

    public async fetch(endpoint: string, options: FetchOptions = {}): Promise<Response>
    {
        let url = `${this.baseUri}${this.normalizeEndpoint(endpoint)}`;
        const headers = Object.assign(
            {},
            {
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
            options.headers ?? {},
        );
        await this.authHolder.prepare(endpoint, headers);
        const method = options.method ?? HttpMethod.GET;
        const fetchParams = {
            method: method,
            mode: "cors",
            cache: "no-cache",
            headers,
        }

        if (method !== HttpMethod.GET) {
            fetchParams['body'] = typeof options.data === 'string'
                ? options.data
                : (
                    fetchParams.headers['Content-Type'] === 'application/json'
                        ? JSON.stringify(options.data)
                        : this.serialize(options.data)
                );
        } else {
            if (options.data) {
                url += `?${this.serialize(options.data)}`;
            }
        }

        try {
            return await fetch(
                url,
                // @ts-ignore
                fetchParams,
            );
        } catch (e) {
            console.error(`[Api] Fetch error: ${e.message ? e.message : e}`, {endpoint, options}, e);

            return Promise.reject(e);
        }
    }

    public async unwrapToApiResult(response: Response): Promise<any> {
        if (response.headers.has('Content-Type') && response.headers.get('Content-Type').includes('application/json')) {
            return response.json();
        }

        const data = await response.text();

        if (data.startsWith("{")) {
            try {
                return JSON.parse(data);
            } catch (e) {
                return Promise.reject(e);
            }
        }

        const isError = response.status > 299;

        return {
            is_success: !isError,
            message: isError
                ? 'Unrecognized response, status: ' + response.statusText
                : data,
        };
    }

    public tryExtractValidationErrors(resp: any): Array<ValidationError> {
        if (resp.is_error && resp.validation) {
            return resp.validation;
        }

        return [];
    }

    protected normalizeEndpoint(endpoint: string): string
    {
        if (!endpoint.startsWith('/')) {
            endpoint = `/${endpoint}`;
        }

        if (endpoint.endsWith('/')) {
            endpoint = endpoint.slice(0, -1);
        }

        return endpoint;
    }

    protected serialize(obj: {}, prefix?: string): string {
        const str = [];

        Object.keys(obj).forEach((p) => {
            if (obj.hasOwnProperty(p)) {
                let k = prefix ? prefix + "[" + p + "]" : p,
                    v = obj[p];

                str.push(
                    (v !== null && typeof v === "object")
                        ? this.serialize(v, k)
                        : encodeURIComponent(k) + "=" + encodeURIComponent(v)
                );
            }
        });

        return str.join("&");
    }
}
