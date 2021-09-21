import template from './ff-restrictions-index.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-restrictions-index', {
    template: template,

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            isCustomFiltersTab: false
        }
    },

    created() {
        this.routerViewTabChanged();
    },

    watch: {
        $route() {
            this.routerViewTabChanged();
        }
    },

    methods: {
        routerViewTabChanged() {
            if (this.$route.name === 'elio.factfinder.restrictions.index.customfilters') {
                this.isCustomFiltersTab = true
            } else {
                this.isCustomFiltersTab = false;
            }
        },

        async onSave() {
            this.isLoading = true;
            this.isSaveSuccessful = false;
            if(this.$refs.routerView.$refs.ruler) {
                await this.$refs.routerView.$refs.ruler.saveAll();
            }
            this.isLoading = false;
            this.isSaveSuccessful = true;
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        }
    }
});