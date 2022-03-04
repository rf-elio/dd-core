import template from './sw-cms-el-ff-advisor-campaign.html.twig';
import './sw-cms-el-ff-advisor-campaign.scss';

Shopware.Component.register('sw-cms-el-ff-advisor-campaign', {
    template,

    mixins: [
        'cms-element'
    ],

    created () {
        this.createdComponent();
    },

    methods: {
        createdComponent () {
            this.initElementConfig('ff-advisor-campaign');
        }
    }
})
