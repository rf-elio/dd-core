import ApiService from 'src/core/service/api.service';

/**
 * @private
 */
export default class ExportService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'ff/export') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'exportService';
        this.httpClient = httpClient;
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
}
