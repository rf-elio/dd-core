import template from './sw-category-detail-override.html.twig';
Shopware.Component.override('sw-category-detail', {
    template,

    methods: {
        onSaveChanges() {
            if (this.$refs.categoryView.$refs.routerView.$refs.ruler) {
                this.$refs.categoryView.$refs.routerView.$refs.ruler.saveAll();
            }
            this.onSave();
        }
    }
});