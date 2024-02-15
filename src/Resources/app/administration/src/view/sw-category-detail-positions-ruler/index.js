import template from './sw-category-detail-positions-ruler.html.twig';

const { Component, Mixin } = Shopware;
const { mapState, mapGetters } = Component.getComponentHelper();
const { Criteria } = Shopware.Data;

Shopware.Component.register('elio-category-detail-positions-ruler', {
    template: template,

    props: {
        isLoading: {
            type: Boolean,
            required: true
        }
    },

    data() {
        return {
            salesChannelId: null
        }
    },

    watch: {
        salesChannelId() {
            if(this.$refs.ruler) {
                this.$refs.ruler.setSalesChannelId(this.salesChannelId);
            }
        }
    },

    computed: {
        ...mapState('swCategoryDetail', [
            'category',
        ]),
        type() {
            return 'navigation-sorting';
        }
    },

    methods: {
        onSalesChannelChanged(salesChannelId) {
            this.salesChannelId = salesChannelId;
        },
    }
});