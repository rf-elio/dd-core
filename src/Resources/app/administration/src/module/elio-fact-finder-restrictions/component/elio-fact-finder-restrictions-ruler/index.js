import template from './elio-fact-finder-restriction-ruler.html.twig';
import '../../page/ff-restrictions-index/ff-restrictions-index.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Shopware.Component.register('ff-restriction-ruler', {
    template: template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        isCategory: {
            type: Boolean,
            required: true,
            default() {
                return false;
            }
        },
        layer: {
            type: String,
            required: false,
            default() {
                return 'global';
            }
        },
        category_id: {
            type: String,
            required: false,
        },
    },

    computed: {
        allowAllChecked() {
            return false;
        },
        blockAllChecked() {
            return false;
        },
        filterRepository() {
            return this.repositoryFactory.create('elio_ff_filter');
        },
        filterRestrictionRepository() {
            return this.repositoryFactory.create('elio_ff_filter_restrictions');
        }
    },

    data() {
        return {
            isLoading: false,
            currentDragItem: null,
            movedFiltersIds: [],
            allList: [],
            allowList: [],
            blockList: []
        }
    },

    created() {
        this.onCreated();
    },

    methods: {
        onCreated() {
            this.loadFilters();
        },

        onDragStart(dragData) {
            if (dragData.target.className === "filter") {
                this.currentDragItem = dragData.target;
            }
        },

        onDrop(dragData) {
            if (dragData.target.classList.contains("ruler-tab-filter-list")) {
                dragData.preventDefault();
                var operator = this;
                var draggedId = this.currentDragItem.getAttribute("data-filter-id");
                var targetColumnType = dragData.target.getAttribute("data-filter-column");
                var fromColumnType = this.currentDragItem.parentNode.getAttribute("data-filter-column");

                this[fromColumnType + 'List'].forEach(function (item, i, arr) {
                    if (item.id === draggedId) {
                        operator[targetColumnType + 'List'].push(item);
                        operator[fromColumnType + 'List'].splice(i, 1);
                    }
                });
            }
        },

        onAllowAllClicked() {
            console.log('onAllowAllClicked');
        },

        onAllowSelectedClicked() {
            console.log('onAllowSelectedClicked');
        },

        onBlockAllClicked() {
            console.log('onBlockAllClicked');
        },

        onBlockSelectedClicked() {
            console.log('onBlockSelectedClicked');
        },

        async loadFilters() {
            this.isLoading = true;
            try {
                var criteria = new Criteria();
                criteria.addAssociation('filters')
                if (this.isCategory && this.category_id != null) {
                    criteria.addFilter(
                        Criteria.equals('elio_ff_filter_restrictions.isCategory', true)
                    );
                    criteria.addFilter(
                        Criteria.equals('elio_ff_filter_restrictions.categoryId', this.category_id)
                    );
                } else {
                    criteria.addFilter(
                        Criteria.equals('elio_ff_filter_restrictions.isCategory', false)
                    );
                    criteria.addFilter(
                        Criteria.equals('elio_ff_filter_restrictions.layer', this.layer)
                    );
                }

                this.movedFiltersIds = [];
                var operator = this;
                await this.filterRestrictionRepository
                    .search(criteria, Shopware.Context.api)
                    .then(filterRestrictions => {
                        filterRestrictions.forEach(function (restrictionColumn) {
                            restrictionColumn.filters.forEach(function (filter) {
                                operator.movedFiltersIds.push(filter.id);
                                if (restrictionColumn.isAllowed) {
                                    operator.allowList.push(filter);
                                } else {
                                    operator.blockList.push(filter);
                                }
                            });
                        });
                    });

                await this.buildFilters();
                this.isLoading = false;
            } catch {
                this.isLoading = false;
                this.$router.push({name: 'elio.factfinder.restrictions.index.global'});
            }
        },

        async buildFilters() {
            var criteria = new Criteria();
            criteria.setLimit(500); /* upddate it */
            if (this.movedFiltersIds.length > 0) {
                criteria.addFilter(Criteria.not(
                    'AND',
                    [
                        Criteria.equalsAny('elio_ff_filter.id', this.movedFiltersIds)
                    ]
                ));
            }
            var operator = this;
            await this.filterRepository
                .search(criteria, Shopware.Context.api)
                .then(filters => {
                    filters.forEach(function (filter) {
                        operator.allList.push(filter);
                    });
                });
        },

        save() {

        }
    }
});