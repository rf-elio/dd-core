import Plugin from 'src/plugin-system/plugin.class';

export default class ElioSearchTrackerPlugin extends Plugin {
    static options = {
        maxHistoryLength: 10
    };

    init() {
        let searchTerm = this._getSearchTerm();

        if (searchTerm) {
            this._storeSearchTerm(searchTerm);
        }
    }

    _getSearchTerm() {
        let searchTerm = {};
        let options = this.options;
        
        if (options) {
            searchTerm = {
                searchTerm: options.searchTerm && options.searchTerm.toLowerCase(),
                searchPageUrl: options.searchPageUrl
            };
        }

        return searchTerm;
    }

    _storeSearchTerm(searchTerm) {
        let searchHistory = JSON.parse(localStorage.getItem('searchHistory')) || [];

        let searchedBefore = searchHistory.some(oldSearchTerm => this._areSearchTermsEqual(oldSearchTerm, searchTerm));
        if (searchedBefore)
            searchHistory = searchHistory.filter(oldSearchTerm => !this._areSearchTermsEqual(oldSearchTerm, searchTerm));

        searchHistory.push(searchTerm);
        let searchHistoryLength = searchHistory.length;
        let maxLength = this.options.maxHistoryLength;
        if (searchHistoryLength > maxLength) {
            searchHistory = searchHistory.slice(searchHistoryLength-maxLength, searchHistoryLength);
        }
        localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
    }

    _areSearchTermsEqual(term1, term2) {
        return term1.searchTerm == term2.searchTerm;
    }
}