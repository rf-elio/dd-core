import FilterPropertySelectPlugin from 'src/plugin/listing/filter-property-select.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import Iterator from 'src/helper/iterator.helper';
import deepmerge from 'deepmerge';
import PluginManagerSingleton from 'src/plugin-system/plugin.manager';

export default class ElioDataDiscoveryFilterTreeSelectPlugin extends FilterPropertySelectPlugin {

    static options = deepmerge(FilterPropertySelectPlugin.options, {
        propertyName: '',
        elioDataDiscoveryFilterName: 'elio-data-discovery-tree',
    });

    getValues() {
        const values = super.getValues();
        values[this.options.elioDataDiscoveryFilterName] = values[this.options.name].slice();
        return values;
    }

    _onChangeFilter(checkbox) {
        if (checkbox.checked === false) {
            checkbox.closest('.category-navigation').querySelectorAll(this.options.checkboxSelector).forEach((checkbox) => {
                if (checkbox.checked === false) {
                    return;
                }
                checkbox.checked = false;
            });
        }
        // reset page to 1 when updating the filter
        this.listing.changeListing(true, { p: 1 });
    }

    _registerEvents() {
        const checkboxes = DomAccess.querySelectorAll(this.el, this.options.checkboxSelector, false);
        if (!checkboxes) {
            return;
        }

        Iterator.iterate(checkboxes, (checkbox) => {
            checkbox.addEventListener('change', this._onChangeFilter.bind(this, checkbox));
        });
    }

    updateOpenedFilter(newFilter) {
        const selector = '.filter-panel-item-dropdown';
        this.el.querySelector(selector).innerHTML = newFilter.querySelector(selector).innerHTML;
        // Delete plugin instance on element so the new instance will be created
        PluginManagerSingleton.getPluginInstancesFromElement(this.el).delete(this._pluginName);
    }

    afterContentChange() {
        if (!this.el.classList.contains('disabled')) {
            this.listing.deregisterFilter(this);
        }
    }
}
