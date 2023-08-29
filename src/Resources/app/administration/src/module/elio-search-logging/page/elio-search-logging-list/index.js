import template from './elio-search-logging-list.html.twig';
import './elio-search-logging-list.scss';

Shopware.Component.register('elio-search-logging-list', {
    template,
    inject: ['elioSearchLogging'],

    data () {
        return {
            logs: [],
            selectedLog: 0,
            contents: [],
            isLoading: false,
            showDeleteModal: false,
            contentsOffset: 0,
            contentsLimit: 5,
            contentsTotal: 0
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
            const selectedLog = this.logs.find(item => item.value === this.selectedLog);
            return selectedLog ? selectedLog.label : '';
        },
        hasLogs () {
            return this.logs.length > 0;
        }
    },

    created () {
        this.loadInitial();
        window.addEventListener('scroll', this.onScroll, true);
    },

    beforeDestroy () {
        window.removeEventListener('scroll', this.onScroll, true);
    },

    methods: {
        loadInitial () {
            this.logs = [];
            this.contents = [];
            this.selectedLog = 0;
            this.contentsOffset = 0;
            this.load();
        },

        load () {
            this.isLoading = true;
            this.elioSearchLogging.getContent(this.selectedLog, {
                offset: this.contentsOffset,
                limit: this.contentsLimit
            }).then(result => {
                if (result.success) {
                    this.logs = result.data.logs.map((item, idx) => {
                        return { label: item, value: idx }
                    });
                    if (this.contentsOffset > 0) {
                        this.contents = this.contents.concat(result.data.contents);
                    } else {
                        this.contents = result.data.contents;
                    }
                    this.contentsTotal = result.data.contentsTotal
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
            this.loadInitial();
        },

        onScroll () {
            const pagination = this.$refs.pagination;

            if (this.isLoading || !this.inViewport(pagination)) {
                return;
            }

            if (((this.contentsOffset + 1) * this.contentsLimit) < this.contentsTotal) {
                this.contentsOffset++;
                this.load();
            }
        },

        inViewport (element) {
            const rect = element.getBoundingClientRect();

            return rect.top > 0 &&
                rect.left > 0 &&
                rect.bottom > 0 &&
                rect.right > 0 &&
                rect.bottom <= window.innerHeight &&
                rect.right <= window.innerWidth;
        }
    }
})
