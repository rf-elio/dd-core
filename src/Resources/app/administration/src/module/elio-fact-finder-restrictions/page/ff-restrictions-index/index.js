import template from './ff-restrictions-index.html.twig';

Shopware.Component.register('ff-restrictions-index', {
    template: template,

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            isCustomFiltersTab: false,
            salesChannelId: null
        }
    },

    created() {
        this.routerViewTabChanged();
    },

    watch: {
        $route() {
            this.routerViewTabChanged();
        },
        salesChannelId() {
            if(this.$refs.routerView) {
                if (this.$refs.routerView.$refs.ruler) {
                    this.$refs.routerView.$refs.ruler.setSalesChannelId(this.salesChannelId);
                }
            }
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

        onSalesChannelChanged(salesChannelId) {
            this.salesChannelId = salesChannelId;
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