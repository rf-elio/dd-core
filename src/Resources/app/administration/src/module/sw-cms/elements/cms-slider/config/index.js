import template from './sw-cms-el-config-edd-cms-slider.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-edd-cms-slider', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created () {
        this.createdComponent();
    },

    data() {
        return {
            isLoading: false,
            presetList: [],
            selectedPreset: null,
        }
    },

    computed: {
        dropdownOptions() {
            return this.presetList
                .map(preset => ({
                    label: this.escapeHTML(preset.name),
                    value: preset.id,
                }));
        }
    },

    methods: {
        createdComponent() {
            this.initElementConfig('edd-cms-slider');

            this.isLoading = true;
            const httpClient = Shopware.Service('syncService').httpClient;
            const url = '/_action/elio-data-discovery/configuration/preset';
            const basicHeaders = {
                Authorization: `Bearer ${Shopware.Context.api.authToken.access}`,
                'Content-Type': 'application/json',
            };

            httpClient
                .get(url, {
                    headers: basicHeaders
                })
                .then((response) => {
                    this.presetList = response.data.presets;
                })
                .catch(() => {
                    this.presetList = [];
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        updateConfig() {
            const selectedPreset = this.presetList.find(
                preset => preset.id === this.selectedPreset
            );

            if (selectedPreset) {
                this.element.config.cmsSliderParameterValue.value = selectedPreset.value;
            }

            this.onElementUpdate(this.element);
        },

        onElementUpdate(element) {
            this.$emit('element-update', element);
        },

        escapeHTML(str) {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    }
})
