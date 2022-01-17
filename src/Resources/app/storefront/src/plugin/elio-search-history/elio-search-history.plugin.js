import Plugin from 'src/plugin-system/plugin.class';
import ArrowNavigationHelper from 'src/helper/arrow-navigation.helper';
import Debouncer from 'src/helper/debouncer.helper';
import DomAccess from 'src/helper/dom-access.helper';

export default class ElioSearchHistoryPlugin extends Plugin {
    static options = {
        searchHistoryResultClassName: 'js-search-history-result',
        searchHistoryResultItemClassName: 'js-result',
        searchHistoryInputFieldSelector: 'input[type=search]',
        searchHistoryButtonFieldSelector: 'button[type=submit]',
        searchHistoryCollapseButtonSelector: '.js-search-toggle-btn',
        channel: '',
        searchPageUrl: ''
    };

    init() {
        this._input = DomAccess.querySelector(this.el, '.header-search-input');

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

    registerSubscribers() {
        const plugin = window.PluginManager.getPluginInstanceFromElement(this.el, 'SearchWidget');
        plugin.$emitter.subscribe('onBodyClick', (event) => this._onFocusOut(event));
    }

    _handleInputEvent() {
        if (this._input.value) {
            this._clearSearchHistory();
        }
        else {
            this._showSearchHistory();
        }
    }

    _onFocus() {
        this._showSearchHistory();
    }

    _onFocusOut(event) {
        let initialEvent = (event.detail && event.detail['e']) || event;
        // early return if click target is the search form or any of it's children or search history dropdown

        if (initialEvent.target.closest('.e-header-search-form') || initialEvent.target.closest(`.${this.options.searchHistoryResultClassName}`)) {
            return;
        }

        this._clearSearchHistory();
    }

    _onInput() {
        this._showSearchHistory();
    }

    _showSearchHistory() {
        if (!this._input.value) {
            this._clearSearchHistory();

            let searchHistory = this._getSearchHistoryReversed();
            if (searchHistory.length) {
                this._appendSearchHistory(searchHistory);
            }
        }
    }

    _clearSearchHistory() {
        const results = document.querySelector(`.${this.options.searchHistoryResultClassName}`);

        if (results) {
            results.remove();
        }
    }

    _getSearchHistory() {
        let searchHistory = JSON.parse(localStorage.getItem('searchHistory')) || {};

        return searchHistory;
    }

    _getSearchHistoryForChannel() {
        let channel = this.options.channel;

        if (channel) {
            let searchHistory = this._getSearchHistory();

            return searchHistory[channel] || [];
        }        

        return [];        
    }

    _getSearchHistoryReversed() {
        let searchHistory = this._getSearchHistoryForChannel();
        let searchHistoryReversed = searchHistory.reverse();

        return searchHistoryReversed;
    }

    _appendSearchHistory(searchHistory) {
        let searchHistoryJSResult = document.createElement('div');
        searchHistoryJSResult.classList.add("search-suggest", "e-search-history", this.options.searchHistoryResultClassName);

        let eSearchHistoryContainer = document.createElement('ul');
        eSearchHistoryContainer.classList.add("col-12", "col-md", "search-suggest-container");

        searchHistory.forEach(searchTerm => {
            let searchTermListItem = document.createElement('li');
            searchTermListItem.classList.add("search-suggest-product", this.options.searchHistoryResultItemClassName);

            let searchTermRow = document.createElement('div');
            searchTermRow.classList.add("row", "align-items-center", "justify-content-space-between", "no-gutters");

            let searchTermRowSearchTerm = document.createElement('a');
            let searchTermLinkHref = document.createAttribute('href');

            let hrefValue = `${this.options.searchPageUrl}${encodeURIComponent(searchTerm)}`;
            searchTermLinkHref.value = hrefValue;
            searchTermRowSearchTerm.setAttributeNode(searchTermLinkHref);
            let searchTermLinkTitle = document.createAttribute('title');
            searchTermLinkTitle.value = searchTerm;
            searchTermRowSearchTerm.setAttributeNode(searchTermLinkTitle);
            searchTermRowSearchTerm.classList.add("col", "search-suggest-product-name", "text-truncate", "search-suggest-product-link", "js-history-term");

            let searchTermTextNode = document.createTextNode(searchTerm);
            searchTermRowSearchTerm.appendChild(searchTermTextNode);

            let searchTermRowRemoveSearchTerm = document.createElement('a');
            searchTermRowRemoveSearchTerm.classList.add("history-remove");
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
            if (target.classList.contains('history-remove')) {
                event.preventDefault();

                let searchTermElement = target.previousElementSibling;
                if (searchTermElement) {
                    let searchText = searchTermElement.getAttribute('title');
                    this._removeFromHistory(searchText);
                }
            }
        });
    }

    _removeFromHistory(searchText) {
        let searchHistory = this._getSearchHistory();
        let channel = this.options.channel;
        if (channel) {
            let searchHistoryForChannel = searchHistory[channel];

            searchHistoryForChannel = searchHistoryForChannel.filter(searchTerm => searchTerm != searchText);
            searchHistory[channel] = searchHistoryForChannel;
            localStorage.setItem('searchHistory', JSON.stringify(searchHistory));

            this._showSearchHistory();
        }
    }
}