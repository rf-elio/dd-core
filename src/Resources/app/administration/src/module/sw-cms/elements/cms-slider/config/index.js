import template from './sw-cms-el-config-edd-cms-slider.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-edd-cms-slider', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created () {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('edd-cms-slider');
        },

        onElementUpdate(element) {
            this.$emit('element-update', element);
        }
    }
})
