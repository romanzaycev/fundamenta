export interface EventEmitter {
    subscribe(eventName: string, fn: Function): Function;
}

export class EventEmitterImpl implements EventEmitter {
    private _events = {};

    subscribe (eventName: string, fn: Function): Function {
        if(!this._events[eventName]) {
            this._events[eventName] = [];
        }

        this._events[eventName].push(fn);

        return () => {
            this._events[eventName] = this
                ._events[eventName]
                .filter(eventFn => fn !== eventFn);
        }
    }

    emit (eventName: string, data: any = undefined) {
        const subscriptions = this._events[eventName];

        if (subscriptions) {
            subscriptions.forEach(fn => {
                fn.call(null, data);
            });
        }
    }
}
