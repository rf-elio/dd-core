import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Debouncer from 'src/helper/debouncer.helper';

/**
 * this class name needs to be the same as in the arrow-navigation.helper.js file
 * @type {string}
 */
const ARROW_NAVIGATION_ACTIVE_CLASS = 'is-active';

export default class ElioSuggestAutocompletePlugin extends Plugin {
    init() {
        this._inputField = DomAccess.querySelector(this.el, 'input[type="search"]');
        this._autoCompleteEl = DomAccess.querySelector(this.el, '.e-autocomplete');

        this.registerObserver();
        this.registerEvents();
    }

    registerObserver() {
        this.mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                if (mutation.type === 'attributes') {
                    if (mutation.target.classList.contains(ARROW_NAVIGATION_ACTIVE_CLASS)) {
                        try {
                            let suggestName = DomAccess.querySelector(mutation.target, '.search-suggest-product-name');

                            if (suggestName) {
                                let autocompleteContent = this._createAutocompleteText(suggestName);

                                this._autoCompleteEl.innerHTML = autocompleteContent;
                            }
                        }
                        catch(err) {
                            // is_active class is set on something that is not suggestion item
                            this._removeAutoComplete();
                        }
                    }
                }
            });
        });

        this.mutationObserver.observe(this.el, {
            attributes: true,
            attributeFilter: ['class'],
            subtree: true
        });
    }

    registerEvents() {
        this._inputField.addEventListener('input', Debouncer.debounce(() => this._removeAutoComplete(), 50),
            {
                capture: true,
                passive: true,
            }
        );
        this._inputField.addEventListener('keydown', event => this._onKeyDown(event));

        this.$emitter.subscribe('afterSuggest', autocomplete => this._afterSuggestLoad(autocomplete));

        this._autoCompleteEl.addEventListener('click', () => this._inputField.focus());
    }

    _afterSuggestLoad(afterSuggestObject) {
        try {
            DomAccess.querySelector(this.el, ".is-active");
        }
        catch(err) {
            // There is no active element so this is the first load after the user enters text
            if (afterSuggestObject && afterSuggestObject.detail.firstElement) {
                let firstElement = afterSuggestObject.detail.firstElement;

                try {
                    if (DomAccess.querySelector(firstElement, '.highlight')) {
                        let autocompleteText = this._createAutocompleteText(firstElement);
                        this._autoCompleteEl.innerHTML = autocompleteText;
                    }
                }
                catch(err) {
                    console.log("There is no exact match in results")
                }
            }
        }

        this._inputField.focus();
    }

    _onKeyDown(event) {
        let code = event.keyCode || event.which;

        // tab key code
        if (code === 9) {
            event.preventDefault();

            let autoCompleteEl = DomAccess.querySelector(this.el, '.e-autocomplete').getInnerHTML().trim();

            // remove span tag
            let newSearch = autoCompleteEl.replace(/<[^>]*>/g, '');
            this._inputField.value = newSearch;
            this._inputField.dispatchEvent(new Event('input'));
        }
    }

    _removeAutoComplete() {
        this._autoCompleteEl.innerHTML = "";
    }

    _createAutocompleteText(productNameHTML) {
        return productNameHTML.getInnerHTML().trim().replaceAll('highlight', 'invisible');
    }
}