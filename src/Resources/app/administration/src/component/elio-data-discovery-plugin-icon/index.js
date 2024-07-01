import template from './icon.html.twig';
const { Component } = Shopware;
Component.register('elio-data-discovery-plugin-icon', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
