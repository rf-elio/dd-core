import FilterRatingSelectPlugin from 'src/plugin/listing/filter-rating-select.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import deepmerge from 'deepmerge';

export default class ElioDataDiscoveryFilterRatingPlugin extends FilterRatingSelectPlugin {

    static options = deepmerge(FilterRatingSelectPlugin.options, {
        elioDataDiscoveryFilterName: 'elio-data-discovery-rating',
    });

    getValues() {
        const values = {};
        const activeRadio = DomAccess.querySelector(this.el, `${this.options.checkboxSelector}:checked`, false);

        this.currentRating = activeRadio.value;
        this._updateCount();

        values[this.options.elioDataDiscoveryFilterName] = [this.currentRating ? this.options.name + '~' + this.currentRating.toString() + '~' + this.options.maxPoints : ''];

        return values;
    }
}