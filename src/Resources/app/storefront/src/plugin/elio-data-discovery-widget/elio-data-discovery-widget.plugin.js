import SearchWidgetPlugin from 'src/plugin/header/search-widget.plugin.js';
import ButtonLoadingIndicator from 'src/utility/loading-indicator/button-loading-indicator.util';
import DomAccess from 'src/helper/dom-access.helper';
import deepmerge from 'deepmerge';

export default class ElioDataDiscoveryWidgetPlugin extends SearchWidgetPlugin {
    static options = deepmerge(SearchWidgetPlugin.options, {
        suggestAutocompleteType: ''
    });

    init() {
        super.init();
        this.suggestOpenClass = 'suggest-open'
        this.$emitter.subscribe('clearSuggestResults', ()=>{
            this.el.parentNode.classList.remove(this.suggestOpenClass)
        });
    }

    /**
     * Close/remove the search/search history results from DOM if user
     * clicks outside the form or the results popover
     * @param {Event} e
     * @private
     */
    _onBodyClick(e) {
        // early return if click target is the search form or any of it's children
        if (e.target.closest(this.options.searchWidgetSelector)) {
            return;
        }

        // early return if click target is the search result or any of it's children
        if (e.target.closest(this.options.searchWidgetResultSelector) || e.target.closest('.js-search-history')) {
            return;
        }
        // remove existing search results popover
        this._clearSuggestResults();

        this.$emitter.publish('onBodyClick', { e });
    }

    /**
     * Process the AJAX suggest and show results
     * @param {string} value
     * @private
     */
    _suggest(value) {
        const url = this._url + encodeURIComponent(value);

        // init loading indicator
        const indicator = new ButtonLoadingIndicator(this._submitButton);
        indicator.create();

        this.$emitter.publish('beforeSearch');

        this._client.abort();
        this._client.get(url, (response) => {
            // remove existing search results popover first
            this._clearSuggestResults();

            // remove indicator
            indicator.remove();

            // attach search results to the DOM
            this.el.insertAdjacentHTML('beforeend', response);

            let firstElement = "";
            let suggestAutocompleteType = this.options.suggestAutocompleteType;
            let listNotEmpty = !this.el.querySelector('.empty');
            if (suggestAutocompleteType && listNotEmpty) {
                try {
                    let searchTermsColumn = DomAccess.querySelector(this.el, `.elio-suggest-block-${suggestAutocompleteType}`);
                    firstElement = DomAccess.querySelector(searchTermsColumn, `li:first-of-type .search-suggest-product-name`);
                }
                catch(err) {
                    console.warn('The search terms column is not configured in administration');
                }
            }
            this.$emitter.publish('afterSuggest', { firstElement });
        });
    }
}