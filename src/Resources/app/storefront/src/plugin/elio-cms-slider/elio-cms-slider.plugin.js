import DomAccess from 'src/helper/dom-access.helper'
import FilterBasePlugin from 'src/plugin/listing/filter-base.plugin'
import ElementReplaceHelper from 'src/helper/element-replace.helper'
import deepmerge from 'deepmerge'

/**
 * Advisor campaign plugin.
 * - Lazy loading support
 * - Campaign interaction
 */
export default class ElioCmsSliderPlugin extends window.PluginBaseClass {
    static options = deepmerge(FilterBasePlugin.options, {
        cmsSlider: '#e-dd-cms-slider-',
        cmsSliderIdAttribute: 'data-elio-data-discovery-cms-slider-id',
        listingSelector: '.cms-element-product-listing'
    })

    init () {
        this._values = {
            cmsSliderId: this.el.getAttribute(this.options.cmsSliderIdAttribute),
            answerPath: ''
        }

        const parentFilterPanelElement = DomAccess.querySelector(document, this.options.parentFilterPanelSelector)

        this.listing = window.PluginManager.getPluginInstanceFromElement(
          parentFilterPanelElement,
          'Listing'
        )

        this.listing.registerFilter(this)
        this._registerEvents()
        this.listing.$emitter.subscribe('Listing/afterRenderResponse', (event) => {
            this._updateAdvisorByListingResponse(event.detail.response)
        })
    }

    /**
     * @public
     */
    reset(id) {}

    /**
     * @public
     */
    resetAll() {

    }

    /**
     * @public
     */
    getValues() {
        return []
    }

    /**
     * Default filter element function -> we don't have any labels to show
     * @public
     * @returns {*[]}
     */
    getLabels() {
        return []
    }

    /**
     * Registers the click event for answers to react on the user selection
     * @private
     */
    _registerEvents () {

    }
}
