import FilterPropertySelectPlugin from 'src/plugin/listing/filter-property-select.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import Iterator from 'src/helper/iterator.helper';
import deepmerge from 'deepmerge';

export default class FactFinderFilterTreeSelectPlugin extends FilterPropertySelectPlugin {

    static options = deepmerge(FilterPropertySelectPlugin.options, {
        propertyName: '',
        ffFilterName: 'ff-tree',
    });

    getValues() {
        this.listing.options.disableEmptyFilter = true;
        const values = super.getValues();
        values[this.options.ffFilterName] = values[this.options.name].slice();
        return values;
    }

    _onChangeFilter(checkbox) {
        if (checkbox.checked === false) {
            const checkboxes = DomAccess.querySelectorAll(checkbox.closest('.category-navigation'), this.options.checkboxSelector);
            Iterator.iterate(checkboxes, (checkbox) => {
                if (checkbox.checked === false) {
                    return;
                }
                checkbox.checked = false;
            });
        }
        // const checkboxes = DomAccess.querySelectorAll(checkbox.children(), this.options.checkboxSelector);
        // reset page to 1 when updating the filter
        this.listing.changeListing(true, { p: 1 });
    }

    _registerEvents() {
        const checkboxes = DomAccess.querySelectorAll(this.el, this.options.checkboxSelector);

        Iterator.iterate(checkboxes, (checkbox) => {
            checkbox.addEventListener('change', this._onChangeFilter.bind(this, checkbox));
        });
    }

    refreshDisabledState(filter) {
        // Prevent disabling if propertyName is not set correctly
        if (this.options.propertyName === '') {
            return;
        }

        const activeItems = [];
        const properties = filter[this.options.name];

        if (!properties || !properties.entities) {
            this.disableFilter();
            return;
        }
        const entities = properties.entities;

        const property = entities.find(entity => entity.translated.name === this.options.propertyName);
        if (property) {
            activeItems.push(...property.options);
        } else {
            this.disableFilter();
            return;
        }

        const actualValues = this.getValues();
        const actualProperties = actualValues[this.options.name];

        if (activeItems.length < 1 && actualProperties.length === 0) {
            this.disableFilter()
            return;
        } else {
            this.enableFilter();
        }

        this.activeItemsTotalHits = [];
        var activeItemsTotalHits = [];
        activeItems.forEach( (item) => {
            activeItemsTotalHits[item.extensions.ff_facet_extension.key] = item.extensions.ff_facet_extension.totalHits
        });
        this.activeItemsTotalHits = activeItemsTotalHits;
        this._disableInactiveFilterOptions(activeItems.map(entity => entity.extensions.ff_facet_extension.key));
    }

    /**
     * @public
     */
    disableOption(input) {
        const listItem = input.closest(this.options.listItemSelector);
        listItem.classList.add('disabled', 'hidden');
        listItem.setAttribute('title', this.options.snippets.disabledFilterText);
        listItem.setAttribute('hidden', 'hidden');
        input.disabled = true;
        listItem.hidden = true;
    }

    /**
     * @public
     */
    enableOption(input) {
        const listItem = input.closest(this.options.listItemSelector);
        listItem.removeAttribute('title', 'hidden');
        listItem.classList.remove('disabled', 'hidden');
        input.disabled = input.classList.contains('e-ff-not-selectable');
        listItem.hidden = false;

        const label = listItem.querySelector('label');
        var count = this.activeItemsTotalHits[label.htmlFor];
        if ((label.innerText.substr(0, label.innerText.lastIndexOf('(')))) {
            label.innerText = (label.innerText.substr(0, label.innerText.lastIndexOf('('))) + '(' + count + ')';
        }
    }

    _disableInactiveFilterOptions(activeItemIds) {
        const checkboxes = DomAccess.querySelectorAll(this.el, this.options.checkboxSelector);
        Iterator.iterate(checkboxes, (checkbox) => {
            if (checkbox.checked === true) {
                this.enableOption(checkbox);
                return;
            }

            if (activeItemIds.includes(checkbox.id)) {
                this.enableOption(checkbox);
            } else {
                this.disableOption(checkbox);
            }
        });
    }
}