import template from './ff-restrictions-global.html.twig';
import '../ff-restrictions-index/ff-restrictions-index.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

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