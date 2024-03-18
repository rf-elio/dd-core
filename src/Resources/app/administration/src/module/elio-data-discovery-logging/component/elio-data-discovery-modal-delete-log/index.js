import template from './elio-data-discovery-modal-delete-log.html.twig';

Shopware.Component.register('elio-data-discovery-modal-delete-log', {
    template,
    inject: ['elioDataDiscoveryLogging'],
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
            this.$emit('elio-data-discovery-close-delete-modal', event);
        },

        deleteLog (event) {
            this.isLoading = true;
            this.elioDataDiscoveryLogging.deleteLog(this.logIndex).then(() => {
                this.$emit('elio-data-discovery-log-deleted', event);
                this.isLoading = false;
            });
        }
    }
})
