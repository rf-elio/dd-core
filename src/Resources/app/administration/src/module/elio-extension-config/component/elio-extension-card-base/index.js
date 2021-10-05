import template from './elio-extension-card-base.html.twig';
import './elio-extension-card-base.scss';

const { Component, Template } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.extend('elio-extension-card-base', 'sw-extension-card-base', {
    template
});