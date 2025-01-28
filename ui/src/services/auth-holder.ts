const VALID_THRESHOLD_SECS = 1200;

export class AuthHolder {
    private token?: string;
    private validTo?: Date;
    private isPreparing = false;
    private waiting: Function[] = [];

    constructor(
        private readonly baseUri: string,
    ) {
        const savedToken = localStorage.getItem('fnda_auth_token');

        if (savedToken) {
            this.token = savedToken;

            const validTo = localStorage.getItem('fnda_auth_token_vt');

            if (validTo) {
                this.validTo = new Date(validTo);
            }
        }
    }

    public hasToken(): boolean {
        return this.token !== null && this.validTo !== null && this.validTo > (new Date());
    }

    public updateByLoginResponse(data: {token?: string, token_valid_to?: string}): void {
        if (data.token && data.token_valid_to) {
            this.token = data.token;
            this.validTo = new Date(data.token_valid_to);

            localStorage.setItem('fnda_auth_token', this.token);
            localStorage.setItem('fnda_auth_token_vt', data.token_valid_to);
        }
    }

    public async prepare(endpoint: string, headers: {}): Promise<void>
    {
        if (endpoint === '/login' || endpoint === 'login') {
            return;
        }

        if (!this.hasToken()) {
            return Promise.reject(new Error('Auth token not found'));
        }

        if ((new Date()).setDate((new Date()).getTime() - VALID_THRESHOLD_SECS) < this.validTo.getTime()) {
            headers['X-Auth-Token'] = this.token;
            return;
        }

        if (this.isPreparing) {
            return new Promise((resolve, reject) => {
                this.waiting.push((err?: any) => {
                    if (!err) {
                        headers['X-Auth-Token'] = this.token;
                        resolve();
                    } else {
                        reject(err);
                    }
                });
            });
        }

        this.isPreparing = true;

        try {
            const response = await fetch(
                `${this.baseUri}/refresh`,
                {
                    method: "POST",
                    mode: "cors",
                    cache: "no-cache",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-Auth-Token": this.token,
                    },
                },
            );

            if (response.status !== 200) {
                return Promise.reject(new Error(`Bad status: ${response.status}`));
            }

            const body = await response.json();

            if (body.hasOwnProperty('data')) {
                this.updateByLoginResponse(body.data);
            }

            headers['X-Auth-Token'] = this.token;

            if (this.waiting.length > 0) {
                this.waiting.forEach((c) => {
                    c();
                });
                this.waiting = [];
            }

            this.isPreparing = false;
        } catch (e) {
            console.error(`[AuthHolder] Fetch error: ${e.message ? e.message : e}`, e);

            if (this.waiting.length > 0) {
                this.waiting.forEach((c) => {
                    c(e);
                });
                this.waiting = [];
            }

            this.isPreparing = false;

            return Promise.reject(e);
        }
    }
}

const authHolder = new AuthHolder(__fndaapp.env.API_BASE_PATH);

export default authHolder;
