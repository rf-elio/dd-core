import Plugin from 'src/plugin-system/plugin.class';

export default class ElioSearchTrackerPlugin extends Plugin {
    static options = {
        maxHistoryLength: 10
    };

    init() {
        let searchTermObject = this._getSearchTermObject();

        if (searchTermObject) {
            let { channel, searchTerm } = searchTermObject;
            this._storeSearchTerm(searchTerm, channel);
        }
    }

    _getSearchTermObject() {
        let searchTermObject = {};
        let options = this.options;
        
        if (options) {
            searchTermObject = {
                searchTerm: options.searchTerm && options.searchTerm.toLowerCase(),
                channel: options.channel
            };
        }

        return searchTermObject;
    }

    _storeSearchTerm(searchTerm, channel) {
        if (channel) {
            let searchHistory = JSON.parse(localStorage.getItem('searchHistory')) || {};

            let sHistoryForChannel = searchHistory[channel] || [];
            let searchedBefore = sHistoryForChannel.some(oldSearchTerm => this._areSearchTermsEqual(oldSearchTerm, searchTerm));
            if (searchedBefore)
                sHistoryForChannel = sHistoryForChannel.filter(oldSearchTerm => !this._areSearchTermsEqual(oldSearchTerm, searchTerm));

            sHistoryForChannel.push(searchTerm);
            let searchHistoryLength = sHistoryForChannel.length;
            let maxLength = this.options.maxHistoryLength;
            if (searchHistoryLength > maxLength) {
                sHistoryForChannel = sHistoryForChannel.slice(searchHistoryLength-maxLength, searchHistoryLength);
            }
            searchHistory[channel] = sHistoryForChannel;

            localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
        }
    }

    _areSearchTermsEqual(term1, term2) {
        return term1 == term2;
    }
}