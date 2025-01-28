// @ts-nocheck

import {container} from "tsyringe";

import {
    Api,
    authHolder,
    Login,
    ProxiedApi,
} from "@/services";

container.register<Api>(Api.name, {
    useValue: new ProxiedApi(
        __fndaapp.env.API_BASE_PATH,
        authHolder,
    ),
});

container.register<Login>(Login.name, {
    useValue: new Login(
        container.resolve<Api>(Api.name),
    ),
});

container.register<Api>('realApi', {
    useValue: new Api(
        __fndaapp.env.API_BASE_PATH,
        authHolder,
    ),
});
