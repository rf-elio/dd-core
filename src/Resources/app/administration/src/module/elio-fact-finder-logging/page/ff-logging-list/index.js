import template from './ff-logging-list.html.twig';
import './ff-logging-list.scss';

Shopware.Component.register('ff-logging-list', {
    template,

    inject: ['ffLogging'],

    data () {
        return {
            logs: [],
            selectedLog: 0,
            content: null,
            isLoading: false
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
                    this.content = result.data.logContent;
                }
                this.isLoading = false;
            });
        }
    }
})
