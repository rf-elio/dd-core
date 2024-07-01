import DomAccess from 'src/helper/dom-access.helper';
import FilterBasePlugin from 'src/plugin/listing/filter-base.plugin'
import deepmerge from 'deepmerge'

export default class ListingPluginExtension extends window.PluginBaseClass {

    static options = deepmerge(FilterBasePlugin.options, {
        listingPropertiesSelector: '.e-elio-data-discovery-listing-properties',
        filterPanelSelector: '.filter-panel-items-container',
        filterPanelActiveSelector: '.filter-panel-active-container',
        filterPanelItemDropdownSelector: '.filter-panel-item-dropdown'
    });

    init () {
        const parentFilterPanelElement = DomAccess.querySelector(document, this.options.parentFilterPanelSelector)
        this.listing = window.PluginManager.getPluginInstanceFromElement(
            parentFilterPanelElement,
            'Listing'
        );
        this._domParser = new DOMParser();

        this._registerEvents();
    }

    _registerEvents () {
        const me = this

        this.listing.$emitter.subscribe('Listing/afterRenderResponse', (event) => {
            const response = this._domParser.parseFromString(event.detail.response, 'text/html');
            me._updateFilterPanel(response);
            me._updateListingProperties(response);
        })
    }

    /**
     * Replace values on page by search widget response. All data attributes that are present in
     * ".e-elio-data-discovery-listing-properties" element will be processed.
     *
     * We iterate all data attributes and search for elements with classes that have a combination of
     * "e-elio-data-discovery-listing-properties" + "-" + dataAttributeName -> e-elio-data-discovery-listing-properties-total.
     *
     * @param response
     * @private
     */
    _updateListingProperties (response) {
        const listingProperties = response.querySelector(this.options.listingPropertiesSelector);
        if (!listingProperties) {
            return;
        }

        for (const attributeName in listingProperties.dataset) {
            if (listingProperties.dataset.hasOwnProperty(attributeName)) {
                const attributeValue = listingProperties.dataset[attributeName];
                const dataAttributeSelector = this.options.listingPropertiesSelector + '-' + attributeName;
                for (const element of document.querySelectorAll(dataAttributeSelector)) {
                    element.innerHTML = attributeValue;
                }
            }
        }
    }

    _updateFilterPanel (response) {
        const newFilterPanel = response.querySelector(this.options.filterPanelSelector);
        const currentFilterPanel = document.querySelector(this.options.filterPanelSelector);
        if (!newFilterPanel || !currentFilterPanel) {
            console.warn('newFilterPanel or currentFilterPanel do not exist', newFilterPanel, currentFilterPanel);
            return;
        }

        this._replaceFilters(newFilterPanel, currentFilterPanel);
        this._disableFilters(newFilterPanel, currentFilterPanel);

        this.listing.refreshRegistry();
        document.$emitter.publish('Listing/afterRenderFilterPanel');
    }

    _replaceFilters (newFilterPanel, currentFilterPanel) {
        const newFilterDropdowns = newFilterPanel.querySelectorAll(this.options.filterPanelItemDropdownSelector);
        let lastInsertedFilter = null;
        newFilterDropdowns.forEach(newFilterDropdown => {
            const currentFilterDropdown = currentFilterPanel.querySelector(`#${newFilterDropdown.id}`);
            const newFilter = newFilterDropdown.parentElement.cloneNode(true);

            if (currentFilterDropdown) {
                const currentFilter = currentFilterDropdown.parentElement;
                if (currentFilterDropdown.classList.contains('show')) {
                    lastInsertedFilter = currentFilter;
                    window.PluginManager.getPluginInstancesFromElement(currentFilter).forEach(plugin => {
                        if (typeof plugin.updateOpenedFilter === 'function') {
                            plugin.updateOpenedFilter(newFilter);
                        }
                    });
                    this._enableFilter(currentFilterDropdown);
                } else {
                    currentFilter.replaceWith(newFilter);
                    lastInsertedFilter = newFilter;
                }
            } else {
                if (lastInsertedFilter) {
                    lastInsertedFilter.after(newFilter);
                } else {
                    currentFilterPanel.prepend(newFilter);
                }
                lastInsertedFilter = newFilter;
            }
        });
    }

    _disableFilters (newFilterPanel, currentFilterPanel) {
        const currentFilterDropdowns = currentFilterPanel.querySelectorAll(this.options.filterPanelItemDropdownSelector);

        currentFilterDropdowns.forEach(currentFilterDropdown => {
            const newFilterDropdown = newFilterPanel.querySelector(`#${currentFilterDropdown.id}`);
            if (!newFilterDropdown) {
                this._disableFilter(currentFilterDropdown);
                currentFilterDropdown.innerHTML = '';
            }
        });
    }

    _disableFilter(filterDropdown) {
        const mainFilterButton = filterDropdown.previousElementSibling;
        mainFilterButton.classList.add('disabled');
        mainFilterButton.setAttribute('disabled', 'disabled');
        filterDropdown.parentElement.classList.add('disabled');
    }

    _enableFilter(filterDropdown) {
        const mainFilterButton = filterDropdown.previousElementSibling;
        mainFilterButton.classList.remove('disabled');
        mainFilterButton.removeAttribute('disabled');
        filterDropdown.parentElement.classList.remove('disabled')
    }
}
