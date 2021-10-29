import template from './sw-category-detail-ruler.html.twig';

const { Component, Mixin } = Shopware;
const { mapState, mapGetters } = Component.getComponentHelper();
const { Criteria } = Shopware.Data;

Shopware.Component.register('elio-category-detail-ruler', {
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
        ])
    },

    methods: {
        onSalesChannelChanged(salesChannelId) {
            this.salesChannelId = salesChannelId;
        },
    }
});