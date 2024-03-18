import Plugin from 'src/plugin-system/plugin.class';

export default class ElioDataDiscoveryTrackerPlugin extends Plugin {
    static options = {
        maxHistoryLength: 10,
        localStorageKey: 'elio-data-discovery-search-history'
    };

    init() {
        let searchTermObject = this._getSearchTermObject();

        if (searchTermObject) {
            let { searchTermId, channel, searchTerm } = searchTermObject;
            this._storeSearchTerm(channel, searchTermId, searchTerm);
        }
    }

    /**
     * Fetches the currently present search term
     * @returns {null|{searchTerm: *, channel: (string|RTCDataChannel|*), key: *}}
     * @private
     */
    _getSearchTermObject() {
        let options = this.options;
        
        if (!options || !options.searchTerm || !options.channel) {
            return null;
        }

        return  {
            searchTermId: options.searchTerm.toLowerCase(),
            searchTerm: options.searchTerm,
            channel: options.channel
        };
    }

    /**
     * Saves the used search term in the users local storage
     * @param searchTerm
     * @param channel
     * @param searchTermId
     * @private
     */
    _storeSearchTerm(channel, searchTermId, searchTerm) {
        let searchHistory = JSON.parse(localStorage.getItem(this.options.localStorageKey) || null) || {};

        if (!searchHistory.hasOwnProperty(channel)) {
            searchHistory[channel] = [];
        }

        let searchTermIdPresent = false;
        for (const searchHistoryEntry of searchHistory[channel]) {
            if (
                typeof (searchHistoryEntry) === 'object' &&
                searchHistoryEntry.hasOwnProperty('searchTermId') &&
                searchHistoryEntry.searchTermId === searchTermId
            ) {
                searchHistoryEntry.lastOccurrence = new Date().toISOString();
                searchTermIdPresent = true;
            }
        }

        if (!searchTermIdPresent) {
            searchHistory[channel].push({
                searchTermId: searchTermId,
                searchTerm: searchTerm,
                lastOccurrence: new Date().toISOString()
            });
        }

        searchHistory[channel].sort(function compareFn(firstEl, secondEl) {
            const firstElDate = new Date(firstEl.lastOccurrence);
            const secondElDate = new Date(secondEl.lastOccurrence);
            return firstElDate > secondElDate ? -1 : 1;
        })

        if(searchHistory[channel].length > this.options.maxHistoryLength) {
            searchHistory[channel] = searchHistory[channel].slice(0, this.options.maxHistoryLength);
        }

        localStorage.setItem(this.options.localStorageKey, JSON.stringify(searchHistory));
    }
}
