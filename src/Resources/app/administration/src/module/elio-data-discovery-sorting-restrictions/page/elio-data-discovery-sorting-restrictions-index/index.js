import template from './elio-data-discovery-sorting-restrictions-index.html.twig';

Shopware.Component.register('elio-data-discovery-sorting-restrictions-index', {
    template: template,

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            isCustomFiltersTab: false
        }
    },

    beforeRouteLeave(to, from, next) {
        this.checkLeaving(to, from, next);
    },

    beforeRouteUpdate(to, from, next) {
        this.checkLeaving(to, from, next);
    },

    created() {
        this.routerViewTabChanged();
    },

    watch: {
        $route() {
            this.routerViewTabChanged();
        }
    },

    methods: {
        checkLeaving(to, from, next) {
            var willLeave = true;
            if (this.$refs.routerView !== null && this.$refs.routerView !== undefined && this.$refs.routerView.$refs.ruler) {
                willLeave = this.$refs.routerView.$refs.ruler.onLeaving(to);
            }
            if (willLeave) {
                next();
            } else {
                next(false);
            }
        },

        routerViewTabChanged() {
            if (this.$route.name === 'elio.data.discovery.sorting.restrictions.index.customfilters') {
                this.isCustomFiltersTab = true
            } else {
                this.isCustomFiltersTab = false;
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