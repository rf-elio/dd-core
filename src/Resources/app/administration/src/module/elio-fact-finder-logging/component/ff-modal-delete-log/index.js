import template from './ff-modal-delete-log.html.twig';

Shopware.Component.register('ff-modal-delete-log', {
    template,

    inject: ['ffLogging'],

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
            this.$emit('ff-close-delete-modal', event);
        },

        deleteLog (event) {
            this.isLoading = true;
            this.ffLogging.deleteLog(this.logIndex).then(result => {
                this.$emit('ff-log-deleted', event);

                this.isLoading = false;
            });
        }
    }
})
