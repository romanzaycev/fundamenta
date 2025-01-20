export class Cache {
    private _data = {};

    constructor(private readonly ttl: number) {
    }

    set(key: string, value: any): any {
        this._data[key] = {
            t: Date.now(),
            v: value,
        };
    }

    get(key: string, defaultValue: any): any {
        if (key in this._data) {
            if (this._data[key].t + this.ttl < Date.now()) {
                return this._data[key].v;
            }

            delete this._data[key];
        }

        return defaultValue;
    }

    reset(key: string): void {
        if (key in this._data) {
            delete this._data[key];
        }
    }
}
