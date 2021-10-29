import FilterRangePlugin from 'src/plugin/listing/filter-range.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import deepmerge from 'deepmerge';

export default class FactFinderFilterRangePlugin extends FilterRangePlugin {

    static options = deepmerge(FilterRangePlugin.options, {
        inputMinValue: '',
        inputMaxValue: '',
        ffFilterName: 'ff-slider',
    });

    /**
     * @return {Object}
     * @public
     */
    getValues() {
        const values = {};

        values[this.options.minKey] = this._inputMin.value;
        values[this.options.maxKey] = this._inputMax.value;
        values[this.options.ffFilterName] = this.options.name + '~' + this._inputMin.value + '~' + this._inputMax.value;

        return values;
    }
    //
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
