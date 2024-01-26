import template from './elio-search-filter-restrictions-search.html.twig';

Shopware.Component.register('elio-search-filter-restrictions-search', {
    template: template,

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        type() {
            return 'search-filter';
        }
    },

    watch: {
        salesChannelId() {
            if (this.$refs.ruler) {
                this.$refs.ruler.setSalesChannelId(this.salesChannelId);
            }
        }
    },

    data() {
        return {
            salesChannelId: null
        }
    },

    methods: {
        onSalesChannelChanged(salesChannelId) {
            this.salesChannelId = salesChannelId;
        }
    }
});