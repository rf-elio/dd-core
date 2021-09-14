import template from './ff-restrictions-navigation.html.twig';
import '../ff-restrictions-index/ff-restrictions-index.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

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