import template from './elio-data-discovery-filter-restrictions-global.html.twig';

Shopware.Component.register('elio-data-discovery-filter-restrictions-global', {
    template: template,

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        type() {
            return 'global-filter';
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