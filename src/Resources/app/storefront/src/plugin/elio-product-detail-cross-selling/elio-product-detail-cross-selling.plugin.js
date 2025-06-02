import HttpClient from 'src/service/http-client.service';
import ObserverUtil from '../../utility/observer.util';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

/**
 * This plugin uses the template extension on detail pages to load product sliders provided by elioDataDiscovery dynamically.
 */
export default class ElioProductDetailCrossSellingPlugin extends window.PluginBaseClass {
    static options = {
        urlAttribute: 'data-e-elio-data-discovery-product-detail-cross-selling-url',
        url: null,
        productDetailCrossSellingSelector: '.product-detail-cross-selling',
        active: window.elioDataDiscovery.global.active === "1"
    }

    init () {
        if (!this.options.active) {
            return;
        }

        this.options.url = this.el.getAttribute(this.options.urlAttribute)
        this._client = new HttpClient();

        const observer = new ObserverUtil();
        const me = this;
        observer.observeElement(this.el, function () {
            me._loadSlider();
        })
    }

    /**
     * Loads the slider into view
     * @private
     */
    _loadSlider() {
        ElementLoadingIndicatorUtil.create(this.el);
        this._client.get(this.options.url, response => {
            try {
                const crossSellingSlider = this.el.querySelector(this.options.productDetailCrossSellingSelector);
                if (crossSellingSlider) {
                    crossSellingSlider.innerHTML = response;
                    window.PluginManager.initializePlugin('ProductSlider', '[data-product-slider]');
                    this.$emitter.publish('elioDataDiscoveryCrossSelling/slidersLoaded', { crossSellingSlider });
                }
            } catch (e) {
                console.error(e)
            } finally {
                ElementLoadingIndicatorUtil.remove(this.el);
            }
        });
    }
}
