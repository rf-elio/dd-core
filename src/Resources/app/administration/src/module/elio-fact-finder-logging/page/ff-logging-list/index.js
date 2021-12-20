import template from './ff-logging-list.html.twig';
import './ff-logging-list.scss';

Shopware.Component.register('ff-logging-list', {
    template,

    inject: ['ffLogging'],

    data () {
        return {
            logs: [],
            selectedLog: 0,
            contents: [],
            isLoading: false,
            showDeleteModal: false
        }
    },

    metaInfo () {
        return {
            title: this.$createTitle()
        };
    },

    watch: {
        selectedLog () {
            this.load();
        }
    },

    computed: {
        selectedLogName () {
            const selectedLog =  this.logs.find(item => item.value === this.selectedLog);
            return selectedLog ? selectedLog.label : '';
        }
    },

    created () {
        this.load();
    },

    methods: {
        load () {
            this.isLoading = true;
            this.ffLogging.getContent(this.selectedLog).then(result => {
                if (result.success) {
                    this.logs = result.data.logs.map((item, idx) => {
                        return { label: item, value: idx }
                    });
                    this.contents = result.data.logContents;
                }

                this.isLoading = false;
            });
        },

        closeDeleteModal () {
            this.showDeleteModal = false;
        },

        openDeleteModal () {
            this.showDeleteModal = true;
        },

        onDeleted () {
            this.closeDeleteModal();
            this.selectedLog = 0;
        }
    }
})
