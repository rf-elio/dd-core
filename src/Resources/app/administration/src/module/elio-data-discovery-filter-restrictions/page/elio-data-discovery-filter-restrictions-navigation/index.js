import template from './elio-data-discovery-filter-restrictions-navigation.html.twig';

Shopware.Component.register('elio-data-discovery-filter-restrictions-navigation', {
    template: template,

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        type() {
            return 'navigation-filter';
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