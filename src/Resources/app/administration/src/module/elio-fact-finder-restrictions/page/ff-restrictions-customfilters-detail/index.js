import template from './ff-restrictions-customfilters-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-restrictions-customfilters-detail', {
    template: template,

    props: {
        customFilterId: {
            type: String,
            required: false,
            default: null
        }
    }
});