import Plugin from 'src/plugin-system/plugin.class';
import TrackingUtil from './../../utility/tracking.util'

export default class TrackingPlugin extends Plugin {
    static options = {
        elioSearchListingBoxSelector: '.elio-search-listing-box',
        productNumberPathAttribute: 'data-elio-search-product-number',
        parentProductIdPathAttribute: 'data-elio-search-parent-product-id',
        labelPathAttribute: 'data-elio-search-label',
        queryPathAttribute: 'data-elio-search-query',
        pagePathAttribute: 'data-elio-search-page',
        pageSizePathAttribute: 'data-elio-search-pageSize',
        campaignPathAttribute: 'data-elio-search-campaign'
    };
    init() {
        this._path = window.elioSearch.tracking.detailPath;
        if(this._path.length <= 0) {
            return;
        }

        this._linkElements = this.el.getElementsByTagName('a')
        this._productNumber = this.el.getAttribute(this.options.productNumberPathAttribute);
        this._parentProductId = this.el.getAttribute(this.options.parentProductIdPathAttribute);
        this._trackingLabel = this.el.getAttribute(this.options.labelPathAttribute);
        this._trackingQuery = this.el.getAttribute(this.options.queryPathAttribute);
        this._trackingPage = this.el.getAttribute(this.options.pagePathAttribute);
        this._trackingPageSize = this.el.getAttribute(this.options.pageSizePathAttribute);
        this._trackingCampaign = this.el.getAttribute(this.options.campaignPathAttribute);
        this._registerEvents()
    }
    _registerEvents() {
        const me = this;
        const parameters = {
            elioSearchProductTrackingData: {
                productNumber: me._productNumber,
                parentProductId: me._parentProductId,
                label: me._trackingLabel,
                query: me._trackingQuery,
                pos: 0,
                page: me._trackingPage,
                pageSize: me._trackingPageSize,
                campaign: me._trackingCampaign
            }
        };
        for (const linkElement of this._linkElements) {
            linkElement.addEventListener('click', function (event) {
                const listingBoxes = me._getListingBoxes();
                parameters.elioSearchProductTrackingData.pos = me._getPosition(listingBoxes);
                const listingSize = listingBoxes.length;
                if (listingSize > parameters.elioSearchProductTrackingData.pageSize) {
                    parameters.elioSearchProductTrackingData.pageSize = listingSize;
                }

                event.preventDefault();
                TrackingUtil.add(me._path, parameters);
                window.location.href = linkElement.href;
            })
        }
    }

    /**
     * Determines the global position of the given element
     * @returns {*}
     * @private
     */
    _getPosition(listingBoxes) {
        let pos = listingBoxes.indexOf(this.el)
        pos = pos < 0 ? 0 : pos;
        return pos + 1;
    }

    /**
     * Loads all listing boxes and provides the result as an array
     * @returns {any[]}
     * @private
     */
    _getListingBoxes() {
        const elements = [];
        const boxes = document.querySelectorAll(this.options.elioSearchListingBoxSelector);

        for(const box of boxes) {
            if (!this._isHidden(box)) {
                elements.push(box)
            }
        }

        return elements;
    }

    /**
     * Checks if the element can be visible on the page or if the element or one if its parents is visible
     * @param elem
     * @returns {boolean|false|*}
     * @private
     */
    _isHidden(elem) {
        const styles = window.getComputedStyle(elem)
        return styles.display === 'none' || styles.visibility === 'hidden' || (elem.parentNode && elem.parentNode.nodeType === Node.ELEMENT_NODE && this._isHidden(elem.parentNode))
    }
}
