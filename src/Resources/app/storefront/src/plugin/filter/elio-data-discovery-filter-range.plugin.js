import FilterRangePlugin from 'src/plugin/listing/filter-range.plugin';
import deepmerge from 'deepmerge';

export default class ElioDataDiscoveryFilterRangePlugin extends FilterRangePlugin {

    static options = deepmerge(FilterRangePlugin.options, {
        rangeUnit: '',
        elioDataDiscoveryFilterName: 'elio-data-discovery-range',
        snippets: {
            filterRangeOutOfRangeLowerBoundErrorMessage: '',
            filterRangeOutOfRangeUpperBoundErrorMessage: '',
        },
    });

    _onChangeInput() {
        clearTimeout(this._timeout);

        this._timeout = setTimeout(() => {
            if (this._isInputInvalid()) {
                this._setError(this._getErrorMessageTemplate('filterRangeErrorMessage') );
            } else if (this._isInputLowerBoundInvalid()) {
                this._setError(this._getErrorMessageTemplate('filterRangeLowerBoundErrorMessage'));
            } else if (this._isOutOfRangeLower()) {
                this._setError(this._getErrorMessageTemplate('filterRangeOutOfRangeLowerBoundErrorMessage'));
            } else if (this._isOutOfRangeUpper()) {
                this._setError(this._getErrorMessageTemplate('filterRangeOutOfRangeUpperBoundErrorMessage'));
            } else {
                this._removeError();
                this.listing.changeListing();
            }
        }, this.options.inputTimeout);
    }

    /**
     * @return {Object}
     * @public
     */
    getValues() {
        const values = {};
        let valuePresent = false;
        if (this._inputMin.value.length > 0) {
            values[this.options.minKey] = this._inputMin.value;
            valuePresent = true;
        }
        if (this._inputMax.value.length > 0) {
            values[this.options.maxKey] = this._inputMax.value;
            valuePresent = true;
        }

        if (valuePresent) {
            values[this.options.elioDataDiscoveryFilterName] = [this.options.name + '~' + this._inputMin.value + '~' + this._inputMax.value];
        }
        return values;
    }

    _isOutOfRangeLower() {
        return parseFloat(this._inputMax.value) < parseFloat(this._inputMax.min);
    }

    _isOutOfRangeUpper() {
        return parseFloat(this._inputMin.value) > parseFloat(this._inputMin.max);
    }

    getLabels() {
        let labels = [];

        if (this._inputMin.value.length || this._inputMax.value.length) {
            if (this._inputMin.value.length) {
                labels.push({
                    label: `${this.options.snippets.filterRangeActiveMinLabel} ${this._inputMin.value} ${this.options.rangeUnit}`,
                    id: this.options.minKey,
                });
            }

            if (this._inputMax.value.length) {
                labels.push({
                    label: `${this.options.snippets.filterRangeActiveMaxLabel} ${this._inputMax.value} ${this.options.rangeUnit}`,
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
