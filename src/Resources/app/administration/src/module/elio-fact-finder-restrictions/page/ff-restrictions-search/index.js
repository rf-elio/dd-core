import template from './ff-restrictions-search.html.twig';

Shopware.Component.register('ff-restrictions-search', {
    template: template,

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        type() {
            return 'search';
        }
    }
});