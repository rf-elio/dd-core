import ObserverUtil from '../../utility/observer.util';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import ElioCmsSliderPlugin from "./elio-cms-slider.plugin";

/**
 * Advisor campaign plugin.
 * - Lazy loading support
 * - Campaign interaction
 */
export default class ElioCmsSliderCmsLoaderPlugin extends window.PluginBaseClass {
    static options = {
        urlAttribute: 'data-elio-data-discovery-cms-slider-url',
        parameterNameAttribute: 'data-elio-data-discovery-cms-slider-parameter-name',
        parameterValueAttribute: 'data-elio-data-discovery-cms-slider-parameter-value',
        containerSelector: '.e-dd-cms-slider-lazy-container',

        cmsSliderItemSelector: '.elio-dd-cms-slider-container-item',
        cmsSliderIdAttribute: 'data-e-dd-cms-slider-id',
    }

    init () {
        this._client = new HttpClient()
        this._url = this.el.getAttribute(this.options.urlAttribute)
        this._cmsSliderContainer = this.el.querySelector(this.options.containerSelector);

        this._request_params = {
            parameterName: this.el.getAttribute(this.options.parameterNameAttribute),
            parameterValue: this.el.getAttribute(this.options.parameterValueAttribute)
        }

        const observer = new ObserverUtil();
        const me = this;
        observer.observeElement(this.el, function () {
            me._load()
        })
    }
}
