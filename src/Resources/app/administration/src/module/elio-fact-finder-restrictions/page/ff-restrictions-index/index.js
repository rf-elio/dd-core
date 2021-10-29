import template from './ff-restrictions-index.html.twig';

Shopware.Component.register('ff-restrictions-index', {
    template: template,

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false
        }
    },

    beforeRouteLeave(to, from, next) {
        this.checkLeaving(to, from, next);
    },

    beforeRouteUpdate(to, from, next) {
        this.checkLeaving(to, from, next);
    },

    methods: {
        checkLeaving(to, from, next) {
            var willLeave = true;
            if (this.$refs.routerView.$refs.ruler) {
                willLeave = this.$refs.routerView.$refs.ruler.onLeaving(to);
            }
            if (willLeave) {
                next();
            } else {
                next(false);
            }
        },

        async onSave() {
            this.isLoading = true;
            this.isSaveSuccessful = false;
            if(this.$refs.routerView.$refs.ruler) {
                await this.$refs.routerView.$refs.ruler.saveAll();
            }
            this.isLoading = false;
            this.isSaveSuccessful = true;
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        }
    }
});