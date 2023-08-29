import template from './sw-cms-el-elio-search-advisor-campaign.html.twig';
import './sw-cms-el-elio-search-advisor-campaign.scss';

Shopware.Component.register('sw-cms-el-elio-search-advisor-campaign', {
    template,

    mixins: [
        'cms-element'
    ],

    created () {
        this.createdComponent();
    },

    methods: {
        createdComponent () {
            this.initElementConfig('elio-search-advisor-campaign');
        }
    }
})
