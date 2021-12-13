const ApiService = Shopware.Classes.ApiService;

export default class LoggingService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'ff/logging') {
        super(httpClient, loginService, apiEndpoint);
    }

    getContent (log = 0) {
        const apiRoute = `_action/${this.getApiBasePath()}/show?log=${log}`;

        return this.httpClient.get(
            apiRoute,
            { headers: this.getBasicHeaders() },
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}
