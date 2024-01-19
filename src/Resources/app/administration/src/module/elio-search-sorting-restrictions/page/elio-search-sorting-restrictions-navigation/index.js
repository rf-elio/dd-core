import template from './elio-search-sorting-restrictions-navigation.html.twig';

Shopware.Component.register('elio-search-sorting-restrictions-navigation', {
    template: template,

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        type() {
            return 'navigation';
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