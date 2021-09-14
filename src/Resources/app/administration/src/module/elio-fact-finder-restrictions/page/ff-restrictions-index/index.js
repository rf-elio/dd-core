import template from './ff-restrictions-index.html.twig';
import './ff-restrictions-index.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-restrictions-index', {
    template: template
});