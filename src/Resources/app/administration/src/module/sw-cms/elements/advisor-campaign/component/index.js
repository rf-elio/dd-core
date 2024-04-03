import template from './sw-cms-el-edd-advisor-campaign.html.twig';
import './sw-cms-el-edd-advisor-campaign.scss';

Shopware.Component.register('sw-cms-el-edd-advisor-campaign', {
    template,

    mixins: [
        'cms-element'
    ],

    created () {
        this.createdComponent();
    },

    methods: {
        createdComponent () {
            this.initElementConfig('edd-advisor-campaign');
        }
    }
})
