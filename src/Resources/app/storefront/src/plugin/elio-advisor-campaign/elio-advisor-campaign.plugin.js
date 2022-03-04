import Plugin from 'src/plugin-system/plugin.class'
import DomAccess from 'src/helper/dom-access.helper'
import FilterBasePlugin from 'src/plugin/listing/filter-base.plugin'
import ElementReplaceHelper from 'src/helper/element-replace.helper'
import deepmerge from 'deepmerge'

/**
 * Advisor campaign plugin.
 * - Lazy loading support
 * - Campaign interaction
 */
export default class ElioAdvisorCampaignPlugin extends Plugin {
    static options = deepmerge(FilterBasePlugin.options, {
        advisorSelector: '#e-ff-advisor-campaign-',
        advisorIdAttribute: 'data-fact-finder-advisor-campaign-id',
        answerSelector: '.e-ff-advisor-campaign-answer',
        answerPathAttribute: 'data-e-ff-advisor-campaign-answer-path'
    })

    init () {
        this._values = {
            campaignId: this.el.getAttribute(this.options.advisorIdAttribute),
            answerPath: ''
        }
        this._advisorSelector = this.options.advisorSelector + this._values.campaignId

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
        this._values.answerPath = ''
    }

    /**
     * @public
     */
    getValues() {
        const values = {}
        if (this._values.answerPath) {
            values['ff-answer-path-' + this._values.campaignId] = this._values.answerPath
        }
        return values
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
        const me = this
        const answers = this.el.querySelectorAll(this.options.answerSelector)

        for (const answer of answers) {
            const answerPath = answer.getAttribute(this.options.answerPathAttribute)
            answer.addEventListener('click', () => {
                me.onSelectAnswer(answerPath)
            })
        }
    }

    /**
     * Updates the advisor element by the given listing response
     * @private
     */
    _updateAdvisorByListingResponse (response) {
        const doc = new DOMParser().parseFromString(response, 'text/html');
        if (doc.querySelectorAll(this._advisorSelector).length <= 0) {
            this.el.style.display = 'none'
            return;
        }

        this.el.style.display = 'block'
        ElementReplaceHelper.replaceFromMarkup(response, this._advisorSelector, false)
        this._registerEvents()
    }

    /**
     * Sets the current answer path and reloads the advisor
     * @param answerPath
     */
    onSelectAnswer(answerPath) {
        this._values.answerPath = answerPath
        this.listing.changeListing()
    }
}
