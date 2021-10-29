/**
 * @private
 */
export default class ExportService {
    constructor(httpClient, loginService) {
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.apiEndpoint = 'ff/export';
        this.contentType = 'application/vnd.api+json';
        this.name = 'exportService';
    }

    /**
     * Download the export file
     *
     * @param exportId {string} Export id
     * @returns {string}
     */
    async getStatus(exportId) {
        return await this.httpClient.get(
          `/_action/ff/export/status/${exportId}`,
          { headers: this.getBasicHeaders() });
    }

    /**
     * Creates the url that can be used to download the export
     * @param exportId {string}
     */
    getDownloadUrl(exportId) {
        return `${Shopware.Context.api.apiPath}/_action/${this.getApiBasePath()}/download/${exportId}`;
    }

    /**
     * Get the basic headers for a request.
     *
     * @param additionalHeaders
     * @returns {Object}
     */
    getBasicHeaders(additionalHeaders = {}) {
        const basicHeaders = {
            Accept: this.contentType,
            Authorization: `Bearer ${this.loginService.getToken()}`,
            'Content-Type': 'application/json',
        };

        return Object.assign({}, basicHeaders, additionalHeaders);
    }

    /**
     * Returns the URI to the API endpoint
     *
     * @param {String|Number} [id]
     * @param {String} [prefix='']
     * @returns {String}
     */
    getApiBasePath(id, prefix = '') {
        let url = '';

        if (prefix?.length) {
            url += `${prefix}/`;
        }

        if (id && id.length > 0) {
            return `${url}${this.apiEndpoint}/${id}`;
        }

        return `${url}${this.apiEndpoint}`;
    }
}
