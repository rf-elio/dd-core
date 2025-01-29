/**
 * @private
 */
export default class SyncProfileService {
    constructor(httpClient, loginService) {
        this.httpClient = httpClient;
        this.loginService = loginService;
        this.apiEndpoint = 'elio-data-discovery/export';
        this.contentType = 'application/vnd.api+json';
        this.name = 'syncProfileService';
    }

    /**
     * Download the export file
     *
     * @param exportId {string} Export id
     * @returns {string}
     */
    async getStatus(exportId) {
        return await this.httpClient.get(
          `/_action/elio-data-discovery/export/status/${exportId}`,
          { headers: this.getBasicHeaders() });
    }

    getProfiles() {
        return this.httpClient.get(
            '/_action/elio-data-discovery/sync-profile/profiles',
            {headers: this.getBasicHeaders()}
        ).then((response) => {
            return response.data;
        });
    }

    /**
     * Creates the url that can be used to download the export
     * @param exportId {string}
     * @param salesChannel
     * @param downloadUsername
     * @param downloadPassword
     */
    getDownloadUrl(exportId, salesChannel, downloadUsername, downloadPassword) {
        let downloadUrl = `${Shopware.Context.api.apiPath}/_action/${this.getApiBasePath()}/download/${exportId}/${salesChannel}`;

        if (downloadUsername && downloadPassword) {
            const basicAuth = encodeURIComponent(downloadUsername) + ':' + encodeURIComponent(downloadPassword) + '@';
            downloadUrl = downloadUrl.replace('http://', 'http://' + basicAuth);
            downloadUrl = downloadUrl.replace('https://', 'https://' + basicAuth);
        }

        return downloadUrl;
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

        if (prefix && prefix.length) {
            url += `${prefix}/`;
        }

        if (id && id.length > 0) {
            return `${url}${this.apiEndpoint}/${id}`;
        }

        return `${url}${this.apiEndpoint}`;
    }

    generate(exportId, options) {
        return this.httpClient.post(
            `/_action/elio-data-discovery/export/generate/${exportId}`,
            options,
            { headers: this.getBasicHeaders() },
        ).then((response) => {
            return response.data;
        });
    }
}
