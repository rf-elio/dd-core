import template from './ff-restrictions-global.html.twig';

Shopware.Component.register('ff-restrictions-global', {
    template: template,

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        type() {
            return 'global';
        }
    }
});