const ApiService = Shopware.Classes.ApiService;

export default class LoggingService extends ApiService {
    constructor (httpClient, loginService, apiEndpoint = 'elio-search/logging') {
        super(httpClient, loginService, apiEndpoint);
    }

    getContent (index = 0, params) {
        const query = Object.keys(params).map(key => key + '=' + params[key]).join('&');
        const apiRoute = `_action/${this.getApiBasePath()}/${index}?${query}`;

        return this.httpClient.get(
            apiRoute,
            { headers: this.getBasicHeaders() },
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    deleteLog (index = 0) {
        const apiRoute = `_action/${this.getApiBasePath()}/${index}`;

        return this.httpClient.delete(
            apiRoute,
            { headers: this.getBasicHeaders() },
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}
