import template from './sw-cms-el-edd-cms-slider.html.twig';
import './sw-cms-el-edd-cms-slider.scss';

Shopware.Component.register('sw-cms-el-edd-cms-slider', {
    template,

    mixins: [
        'cms-element'
    ],

    created () {
        this.createdComponent();
    },

    methods: {
        createdComponent () {
            this.initElementConfig('edd-cms-slider');
        }
    }
})
