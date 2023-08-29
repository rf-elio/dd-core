import FilterRangePlugin from 'src/plugin/listing/filter-range.plugin';
import deepmerge from 'deepmerge';

export default class ElioSearchFilterRangePlugin extends FilterRangePlugin {

    static options = deepmerge(FilterRangePlugin.options, {
        inputMinValue: '',
        inputMaxValue: '',
        rangeUnit: '',
        elioSearchFilterName: 'elio-search-slider',
    });

    /**
     * @return {Object}
     * @public
     */
    getValues() {
        const values = {};

        values[this.options.minKey] = this._inputMin.value;
        values[this.options.maxKey] = this._inputMax.value;
        values[this.options.elioSearchFilterName] = [this.options.name + '~' + this._inputMin.value + '~' + this._inputMax.value];

        return values;
    }

    /**
     * @return {boolean}
     * @private
     */
    _isInputInvalid() {
        let inputMinValue = parseFloat(this._inputMin.value);
        let inputMaxValue = parseFloat(this._inputMax.value);
        return inputMinValue > inputMaxValue
            || inputMinValue < this.options.inputMinValue
            || inputMaxValue > this.options.inputMaxValue;
    }

    getLabels() {
        let labels = [];

        if (this._inputMin.value.length || this._inputMax.value.length) {
            if (this._inputMin.value.length) {
                labels.push({
                    label: `${this.options.name} ${this.options.snippets.filterRangeActiveMinLabel} ${this._inputMin.value} ${this.options.rangeUnit}`,
                    id: this.options.minKey,
                });
            }

            if (this._inputMax.value.length) {
                labels.push({
                    label: `${this.options.name} ${this.options.snippets.filterRangeActiveMaxLabel} ${this._inputMax.value} ${this.options.rangeUnit}`,
                    id: this.options.maxKey,
                });
            }
        } else {
            labels = [];
        }

        return labels;
    }

    /**
     * @param id
     * @public
     */
    reset(id) {
        if (id === this.options.minKey) {
            this._inputMin.value = this.options.inputMinValue;
        }

        if (id === this.options.maxKey) {
            this._inputMax.value = this.options.inputMaxValue;
        }

        this._removeError();
    }

    /**
     * @public
     */
    resetAll() {
        this._inputMin.value = this.options.inputMinValue;
        this._inputMax.value = this.options.inputMaxValue;
        this._removeError();
    }
}
