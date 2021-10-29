import template from './ff-restrictions-navigation.html.twig';

Shopware.Component.register('ff-restrictions-navigation', {
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
    }
});