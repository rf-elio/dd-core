import Plugin from 'src/plugin-system/plugin.class';
import ArrowNavigationHelper from 'src/helper/arrow-navigation.helper';
import Debouncer from 'src/helper/debouncer.helper';
import DomAccess from 'src/helper/dom-access.helper';

export default class ElioSearchHistoryPlugin extends Plugin {
    static options = {
        localStorageKey: 'eff-search-history',
        searchHistoryResultClassName: 'js-search-history-result',
        searchHistoryResultItemClassName: 'js-result',
        searchHistoryInputFieldSelector: 'input[type=search]',
        searchHistoryButtonFieldSelector: 'button[type=submit]',
        searchHistoryCollapseButtonSelector: '.js-search-toggle-btn',
        channel: '',
        searchPageUrl: ''
    };

    init() {
        try {
            this._input = DomAccess.querySelector(this.el, '.header-search-input');
        } catch (e) {
            if (localStorage) {
                let eDebugOutput = localStorage.getItem("eDebug");
                if (eDebugOutput && parseInt(eDebugOutput) === 1) {
                    console.log('SearchHistoryPlugin', e)
                }
            }
            return false
        }
        // initialize the arrow navigation
        this._navigationHelper = new ArrowNavigationHelper(
            this._input,
            `.${this.options.searchHistoryResultClassName}`,
            `.${this.options.searchHistoryResultItemClassName}`,
            true,
        );

        this.registerEvents();
        this.registerSubscribers();
    }

    /**
     * Registers the events to show the search history
     */
    registerEvents() {
        this._input.addEventListener('click', event => this._onFocus(event));

        this._input.addEventListener(
            'input',
            Debouncer.debounce(this._handleInputEvent.bind(this), 250),
            {
                capture: true,
                passive: true,
            },
        );
    }

    /**
     * Registers the events to hide the history if the whitespace was clicked
     */
    registerSubscribers() {
        const plugin = window.PluginManager.getPluginInstanceFromElement(this.el, 'SearchWidget');
        plugin.$emitter.subscribe('onBodyClick', (event) => this._onFocusOut(event));
    }

    /**
     * Shows or hides the search history depending on the search term
     * @private
     */
    _handleInputEvent() {
        if (this._input.value) {
            this._clearSearchHistory();
        } else {
            this._showSearchHistory();
        }
    }

    /**
     * Shows the search history if the focus is in the search field
     * @private
     */
    _onFocus() {
        this._showSearchHistory();
    }

    /**
     * Hides the history if the search input forcus got lost
     * @param event
     * @private
     */
    _onFocusOut(event) {
        let initialEvent = (event.detail && event.detail['e']) || event;
        // early return if click target is the search form or any of it's children or search history dropdown

        if (initialEvent.target.closest('.e-header-search-form') || initialEvent.target.closest(`.${this.options.searchHistoryResultClassName}`)) {
            return;
        }

        this._clearSearchHistory();
    }

    /**
     * Reacting to empty value
     * @private
     */
    _onInput() {
        this._showSearchHistory();
    }

    /**
     * Shows the search history
     * @private
     */
    _showSearchHistory() {
        if (this._input.value) {
            return;
        }

        this._clearSearchHistory();
        let channel = this.options.channel;
        let searchHistory = this._getSearchHistory();
        let searchHistoryForChannel = [];

        if (channel && searchHistory.hasOwnProperty(channel)) {
            searchHistoryForChannel = searchHistory[channel];
        }

        if (searchHistoryForChannel.length > 0) {
            this._appendSearchHistory(searchHistoryForChannel);
        }
    }

    /**
     * Removes the search history view
     * @private
     */
    _clearSearchHistory() {
        const results = document.querySelector(`.${this.options.searchHistoryResultClassName}`);

        if (results) {
            results.remove();
        }
    }

    /**
     * Loads the search history from local storage
     * @returns {*[]}
     * @private
     */
    _getSearchHistory() {
        return JSON.parse(localStorage.getItem(this.options.localStorageKey)) || {};
    }

    /**
     * Appends the search history from local storage
     * @param searchHistory
     * @private
     */
    _appendSearchHistory(searchHistory) {
        let searchHistoryJSResult = document.createElement('div');
        searchHistoryJSResult.classList.add("search-suggest", "e-search-history", this.options.searchHistoryResultClassName);

        let eSearchHistoryContainer = document.createElement('ul');
        eSearchHistoryContainer.classList.add("col-12", "col-md", "search-suggest-container");

        searchHistory.forEach((searchHistory, searchHistoryIndex) => {
            if (!searchHistory.hasOwnProperty('searchTerm')) {
                return;
            }

            const searchTerm = searchHistory.searchTerm;

            let searchTermListItem = document.createElement('li');
            searchTermListItem.classList.add("search-suggest-product", this.options.searchHistoryResultItemClassName);

            let searchTermRow = document.createElement('div');
            searchTermRow.classList.add("row", "align-items-center", "justify-content-space-between", "no-gutters");

            let searchTermRowSearchTerm = document.createElement('a');
            let searchTermLinkHref = document.createAttribute('href');

            searchTermLinkHref.value = `${this.options.searchPageUrl}${encodeURIComponent(searchTerm)}`;
            searchTermRowSearchTerm.setAttributeNode(searchTermLinkHref);
            let searchTermLinkTitle = document.createAttribute('title');
            searchTermLinkTitle.value = searchTerm;
            searchTermRowSearchTerm.setAttributeNode(searchTermLinkTitle);
            searchTermRowSearchTerm.classList.add("col", "search-suggest-product-name", "text-truncate", "search-suggest-product-link", "js-history-term");

            let searchTermTextNode = document.createTextNode(searchTerm);
            searchTermRowSearchTerm.appendChild(searchTermTextNode);

            let searchTermRowRemoveSearchTerm = document.createElement('a');
            searchTermRowRemoveSearchTerm.setAttribute('searchHistoryIndex', searchHistoryIndex);
            searchTermRowRemoveSearchTerm.classList.add('history-remove');
            let searchTermRowRemoveSearchTermHref = document.createAttribute('href');
            searchTermRowRemoveSearchTermHref.value = `#`;
            searchTermRowRemoveSearchTerm.setAttributeNode(searchTermRowRemoveSearchTermHref);

            searchTermRow.appendChild(searchTermRowSearchTerm);
            searchTermRow.appendChild(searchTermRowRemoveSearchTerm);

            searchTermListItem.appendChild(searchTermRow);
            eSearchHistoryContainer.appendChild(searchTermListItem);
        });

        searchHistoryJSResult.appendChild(eSearchHistoryContainer);
        this.el.appendChild(searchHistoryJSResult);

        this._wrapper = document.querySelector('.e-search-history');
        this._wrapper.addEventListener('click', event => {
            let target = event.target;
            if (
                !target.classList.contains('history-remove') ||
                !target.hasAttribute('searchHistoryIndex')
            ) {
                return;
            }

            event.preventDefault();
            this._removeFromHistory(target.getAttribute('searchHistoryIndex'));
        });
    }

    /**
     * Removes a specific item from the search history
     * @param searchHistoryIndex
     * @private
     */
    _removeFromHistory(searchHistoryIndex) {
        let searchHistory = this._getSearchHistory();
        let channel = this.options.channel;
        if (!channel) {
            return;
        }

        searchHistory[channel].splice(searchHistoryIndex, 1);
        localStorage.setItem(this.options.localStorageKey, JSON.stringify(searchHistory));
        this._showSearchHistory();
    }
}
