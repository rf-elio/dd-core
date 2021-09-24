import FilterPropertySelectPlugin from 'src/plugin/listing/filter-property-select.plugin';
import deepmerge from 'deepmerge';

export default class FactFinderFilterPropertySelectPlugin extends FilterPropertySelectPlugin {

    static options = deepmerge(FilterPropertySelectPlugin.options, {
        propertyName: '',
        ffFilterName: 'ff-default',
    });

    getValues() {
        const values = super.getValues();
        values[this.options.ffFilterName] = values[this.options.name].slice();
        return values;
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

        if(actualProperties.length > 0) {
            return;
        }

        this._disableInactiveFilterOptions(activeItems.map(entity => entity.extensions.ff_facet_extension.key));
    }
}