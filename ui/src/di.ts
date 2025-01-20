// @ts-nocheck

import {container} from "tsyringe";

import {Api} from "@/services/api";
import {ProxiedApi} from "@/services/proxied-api";

container.register<Api>(Api.name, {
    useValue: new ProxiedApi(__fndaapp.env.API_BASE_URI),
});

container.register<Api>('realApi', {
    useValue: new Api(__fndaapp.env.API_BASE_URI),
});
