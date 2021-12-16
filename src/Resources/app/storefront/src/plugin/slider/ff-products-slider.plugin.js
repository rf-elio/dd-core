import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import PluginManager from 'src/plugin-system/plugin.manager';
import Debouncer from 'src/helper/debouncer.helper';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

const SCROLL_DEBOUNCE = 100;

export default class FfProductsSliderPlugin extends Plugin {
    static options = {
        url: null,
        requestParams: {},
        tabSelector: '[data-toggle="tab"]',
        sliderSelector: '[data-product-slider]',
        pluginSliderName: 'ProductSlider',
    }

    init () {
        this._isLoading = false;
        this._isLoaded = false;
        this._client = new HttpClient();

        this._registerEvents();

        document.dispatchEvent(new Event('scroll'));
    }

    _registerEvents () {
        document.addEventListener('scroll', Debouncer.debounce(this._onScrollEnd.bind(this), SCROLL_DEBOUNCE));
        $(this.options.tabSelector).on('shown.bs.tab', this._onScrollEnd.bind(this));
    }

    _onScrollEnd (e) {
        if (this._elIsInViewport()) {
            this._fetchProducts();
        }
    }

    _elIsInViewport() {
        const rect = this.el.getBoundingClientRect();

        return rect.top > 0 &&
            rect.left > 0 &&
            rect.bottom > 0 &&
            rect.right > 0 &&
            rect.bottom <= window.innerHeight &&
            rect.right <= window.innerWidth;
    }

    _fetchProducts() {
        if (this._isLoading || this._isLoaded) {
            return;
        }

        this._setLoading(true);
        this._client.post(this.options.url, JSON.stringify(this.options.requestParams), response => {
            response = JSON.parse(response);

            this._setLoading(false);
            this._isLoaded = true;

            if (response.success) {
                this.el.innerHTML = response.data.content;
                PluginManager.initializePlugin(this.options.pluginSliderName, this.options.sliderSelector);
            } else {
                console.log(response.message);
            }
        });
    }

    _setLoading (value) {
        this._isLoading = value;
        if (this._isLoading) {
            ElementLoadingIndicatorUtil.create(this.el);
        } else {
            ElementLoadingIndicatorUtil.remove(this.el);
        }
    }
}
