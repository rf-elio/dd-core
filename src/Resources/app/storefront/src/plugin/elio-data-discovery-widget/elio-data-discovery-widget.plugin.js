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
            this._modifyBounds(document.querySelector('.search-suggest-container'));
            this.$emitter.publish('afterSuggest', { firstElement });
        });
    }

    _modifyBounds(element){
        if(!element){
            return false;
        }
        if(!this.el.parentNode.classList.contains(this.suggestOpenClass)){
            this.el.parentNode.classList.add(this.suggestOpenClass);
        }
        //element is suggest result
        element.style.transform = '';
        let elmRects = element.getBoundingClientRect();
        let searchRects = this.el.getBoundingClientRect();
        let docRects = document.documentElement.getBoundingClientRect();
        let offBound = 0;
        let halfWidth = Math.round(((elmRects.width/2)) - (searchRects.width/2));

        if(elmRects.width > searchRects.width){
            if (elmRects.left + elmRects.width + halfWidth > docRects.width) {
                //off bounds right side
                let topRight = elmRects.left + elmRects.width;
                let overhead = Math.round(topRight - docRects.width);
                offBound = -1 * (overhead + 10);
            } else if (elmRects.left - halfWidth < 0) {
                //get offbounds left side
                offBound = halfWidth - 10;
            }
            if (offBound) {
                //element.style.transform = 'translateX(calc(-50% + ' + (offBound) +'px))';
                element.style.transform = 'translateX(calc(-50%))';
            }
        }
    }
}