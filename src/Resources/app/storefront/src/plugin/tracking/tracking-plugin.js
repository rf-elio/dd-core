import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class TrackingPlugin extends Plugin {
    static options = {
        idPathAttribute: 'data-elio-ff-id',
        productNumberPathAttribute: 'data-elio-ff-productNumber',
        labelPathAttribute: 'data-elio-ff-label',
        queryPathAttribute: 'data-elio-ff-query',
        posPathAttribute: 'data-elio-ff-pos',
        pagePathAttribute: 'data-elio-ff-page',
        pageSizePathAttribute: 'data-elio-ff-pageSize',
        campaignPathAttribute: 'data-elio-ff-campaign',
        updatePathAttribute: 'data-elio-ff-path'
    };
    init() {
        this._client = new HttpClient()
        this._path = this.el.getAttribute(this.options.updatePathAttribute);
        if(this._path.length > 0) {
            this._linkElements = this.el.getElementsByTagName('a')
            this._trackingId = this.el.getAttribute(this.options.idPathAttribute);
            this._trackingProductNumber = this.el.getAttribute(this.options.productNumberPathAttribute);
            this._trackingLabel = this.el.getAttribute(this.options.labelPathAttribute);
            this._trackingQuery = this.el.getAttribute(this.options.queryPathAttribute);
            this._trackingPos = this.el.getAttribute(this.options.posPathAttribute);
            this._trackingPage = this.el.getAttribute(this.options.pagePathAttribute);
            this._trackingPageSize = this.el.getAttribute(this.options.pageSizePathAttribute);
            this._trackingCampaign = this.el.getAttribute(this.options.campaignPathAttribute);
            this._registerEvents()
        }
    }
    _registerEvents() {
        const me = this;
        const parameters = {
            ffProductTrackingData: {
                id: me._trackingId,
                productNumber: me._trackingProductNumber,
                label: me._trackingLabel,
                query: me._trackingQuery,
                pos: me._trackingPos,
                page: me._trackingPage,
                pageSize: me._trackingPageSize,
                campaign: me._trackingCampaign
            }
        };
        for(const linkElement of this._linkElements) {
            linkElement.addEventListener('click', function (event) {
                event.preventDefault();
                me._postTracking(parameters).then(() =>{
                    window.location.href = linkElement.href;
                })
            })
        }
    }
    _postTracking(parameters) {
        const me = this;
        return new Promise((resolve) => {
            me._client.post(me._path, JSON.stringify(parameters), function(responseText) {
                resolve();
            });
        })
    }
}
