import template from './elio-search-modal-delete-log.html.twig';

Shopware.Component.register('elio-search-modal-delete-log', {
    template,
    inject: ['elioSearchLogging'],
    data () {
        return {
            isLoading: false
        }
    },

    props: {
        logIndex: {
            type: Number,
            required: true
        },
        logName: {
            type: String,
            required: true
        }
    },

    methods: {
        closeDeleteModal (event) {
            this.$emit('elio-search-close-delete-modal', event);
        },

        deleteLog (event) {
            this.isLoading = true;
            this.elioSearchLogging.deleteLog(this.logIndex).then(() => {
                this.$emit('elio-search-log-deleted', event);
                this.isLoading = false;
            });
        }
    }
})
