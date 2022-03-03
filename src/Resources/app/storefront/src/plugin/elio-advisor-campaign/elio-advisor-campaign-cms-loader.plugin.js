import Plugin from 'src/plugin-system/plugin.class';
import ObserverUtil from '../../utility/observer.util';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import TrackingPlugin from "../tracking/tracking-plugin";
import ElioAdvisorCampaignPlugin from "./elio-advisor-campaign.plugin";

/**
 * Advisor campaign plugin.
 * - Lazy loading support
 * - Campaign interaction
 */
export default class ElioAdvisorCampaignCmsLoaderPlugin extends Plugin {
    static options = {
        urlAttribute: 'data-fact-finder-advisor-campaign-url',
        parameterNameAttribute: 'data-fact-finder-advisor-campaign-parameter-name',
        parameterValueAttribute: 'data-fact-finder-advisor-campaign-parameter-value',
        containerSelector: '.e-ff-advisor-campaign-lazy-container',

        containerItemSelector: '.elio-ff-advisor-campaign-container-item',
        campaignIdAttribute: 'data-e-ff-advisor-campaign-id',
        answerSelector: '.elio-ff-advisor-campaign-answer',
        answerPathAttribute: 'data-e-ff-advisor-campaign-answer-path'
    }

    init () {
        this._client = new HttpClient()
        this._url = this.el.getAttribute(this.options.urlAttribute)
        this._campaignContainer = this.el.querySelector(this.options.containerSelector);

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

    /**
     * Loads the advisor campaign with the current request parameters
     * @private
     */
    _load () {
        ElementLoadingIndicatorUtil.create(this.el);
        this._client.post(this._url, JSON.stringify(this._request_params), response => {
            this._campaignContainer.innerHTML = response
            PluginManager.initializePlugins();
            ElementLoadingIndicatorUtil.remove(this.el)
        })
    }
}
