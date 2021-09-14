import template from './ff-restrictions-customfilters.html.twig';
import '../ff-restrictions-index/ff-restrictions-index.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-restrictions-customfilters', {
    template: template
});