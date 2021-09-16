import template from './sw-category-detail-override.html.twig';
Shopware.Component.override('sw-category-detail', {
    template,

    methods: {
        onSaveChanges() {
            console.log(this.$refs.categoryView.$refs.routerView.$refs.ruler);

            this.onSave();
        }
    }
});