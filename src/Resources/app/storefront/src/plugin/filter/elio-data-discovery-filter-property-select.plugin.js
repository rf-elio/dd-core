import FilterPropertySelectPlugin from 'src/plugin/listing/filter-property-select.plugin';
import deepmerge from 'deepmerge';
import PluginManagerSingleton from 'src/plugin-system/plugin.manager';

export default class ElioDataDiscoveryFilterPropertySelectPlugin extends FilterPropertySelectPlugin {

    static options = deepmerge(FilterPropertySelectPlugin.options, {
        propertyName: '',
        elioDataDiscoveryFilterName: 'elio-data-discovery-default',
    });

    getValues() {
        const values = super.getValues();
        values[this.options.elioDataDiscoveryFilterName] = values[this.options.name].slice();
        return values;
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
