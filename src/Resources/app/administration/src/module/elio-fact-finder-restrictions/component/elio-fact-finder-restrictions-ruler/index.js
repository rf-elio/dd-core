import template from './elio-fact-finder-restriction-ruler.html.twig';
import './elio-fact-finder-restriction-ruller.scss';

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
        categoryId: {
            type: String,
            required: false,
            default() {
                return '';
            }
        },
    },

    computed: {
        filterRepository() {
            return this.repositoryFactory.create('elio_ff_filter');
        },
        filterRestrictionRepository() {
            return this.repositoryFactory.create('elio_ff_filter_restrictions');
        },
        filterRestrictionFilterRepository() {
            return this.repositoryFactory.create('elio_ff_filter_restrictions_filters');
        },
    },

    data() {
        return {
            isLoading: false,
            currentDragItem: null,
            movedFiltersIds: [],
            allList: [],
            allowList: [],
            blockList: [],
            allowListRestrictionId: '',
            blockListRestrictionId: '',
            allowAllChecked: false,
            blockAllChecked: false,
            limitForCriteria: 500
        }
    },

    created() {
        this.onCreated();
    },

    methods: {
        onCreated() {
            // console.log('isCategory', this.isCategory);
            // console.log('layer', this.layer);
            // console.log('categoryId', this.categoryId);
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
            if(this.allowListRestrictionId !== '') {
                this.turnIsAllChecked(this.allowListRestrictionId, true);
            }
        },

        onAllowSelectedClicked() {
            if(this.allowListRestrictionId !== '') {
                this.turnIsAllChecked(this.allowListRestrictionId, false);
            }
        },

        onBlockAllClicked() {
            if(this.blockListRestrictionId !== '') {
                this.turnIsAllChecked(this.blockListRestrictionId, true);
            }
        },

        onBlockSelectedClicked() {
            if(this.blockListRestrictionId !== '') {
                this.turnIsAllChecked(this.blockListRestrictionId, true);
            }
        },

        turnIsAllChecked(restrictionId, _isAllChecked) {
            //this.isLoading = true;
            try {
                var operator = this;
                this.filterRestrictionRepository.get(restrictionId, Shopware.Context.api)
                    .then((restriction) => {
                        restriction.isAllChecked = _isAllChecked;
                        operator.filterRestrictionRepository.save(restriction, Shopware.Context.api)
                            .then(() => {
                                operator.isLoading = false;
                            });
                    });
            } catch {}
        },

        async loadFilters() {
            this.isLoading = true;
            try {
                var criteria = new Criteria();
                criteria.addAssociation('filters')
                if (this.isCategory && this.categoryId != null) {
                    criteria.addFilter(
                        Criteria.multi(
                            'AND',
                            [
                                Criteria.equals('elio_ff_filter_restrictions.isCategory', true),
                                Criteria.equals('elio_ff_filter_restrictions.categoryId', this.categoryId)
                            ]
                        )
                    );
                } else {
                    criteria.addFilter(
                        Criteria.multi(
                            'AND',
                            [
                                Criteria.equals('elio_ff_filter_restrictions.isCategory', false),
                                Criteria.equals('elio_ff_filter_restrictions.layer', this.layer)
                            ]
                        )
                    );
                }

                this.movedFiltersIds = [];
                var operator = this;
                await this.filterRestrictionRepository
                    .search(criteria, Shopware.Context.api)
                    .then(filterRestrictions => {
                        filterRestrictions.forEach(function (restrictionColumn) {
                            if (restrictionColumn.isAllowed) {
                                operator.allowListRestrictionId = restrictionColumn.id;
                                operator.allowAllChecked = restrictionColumn.isAllChecked;
                            } else {
                                operator.blockListRestrictionId = restrictionColumn.id;
                                operator.blockAllChecked = restrictionColumn.isAllChecked;
                            }
                            restrictionColumn.filters.forEach(function (filter) {
                                operator.movedFiltersIds.push(filter.id);
                                if (restrictionColumn.isAllowed) {
                                    operator.allowList.push(filter);
                                } else {
                                    operator.blockList.push(filter);
                                }
                            });
                        });

                        // Creating filterRestrictionsEntities if there is no-one into elio_ff_filter_restrictions
                        if (this.allowListRestrictionId === '') {
                            var filterRestriction = this.filterRestrictionRepository.create(Shopware.Context.api);

                            filterRestriction.isCategory = this.isCategory;
                            if (!this.isCategory) {
                                filterRestriction.layer = this.layer;
                            } else {
                                filterRestriction.categoryId = this.categoryId;
                            }
                            filterRestriction.isAllowed = true;
                            filterRestriction.isAllChecked = false;

                            this.filterRestrictionRepository.save(filterRestriction, Shopware.Context.api)
                                .then((response) => {
                                    var id = JSON.parse(response.config.data).id;
                                    if (id) {
                                        operator.allowListRestrictionId = id;
                                    }
                                });
                        }
                        if (this.blockListRestrictionId === '') {
                            filterRestriction = this.filterRestrictionRepository.create(Shopware.Context.api);

                            filterRestriction.isCategory = this.isCategory;
                            if (!this.isCategory) {
                                filterRestriction.layer = this.layer;
                            } else {
                                filterRestriction.categoryId = this.categoryId;
                            }
                            filterRestriction.isAllowed = false;
                            filterRestriction.isAllChecked = false;

                            this.filterRestrictionRepository.save(filterRestriction, Shopware.Context.api)
                                .then((response) => {
                                    var id = JSON.parse(response.config.data).id;
                                    if (id) {
                                        operator.blockListRestrictionId = id;
                                    }
                                });
                        }
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
            criteria.setLimit(this.limitForCriteria); /* upddate it */
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

        async saveAll() {
            this.isLoading = true;
            var operator = this;
            var entities = [];

            // Removing old links from elio_ff_filter_restrictions_filters table
            var criteria = new Criteria();
            criteria.addAssociation('filters');
            criteria.addFilter(
                Criteria.multi(
                    'OR',
                    [
                        Criteria.equals('elio_ff_filter_restrictions.id', this.allowListRestrictionId),
                        Criteria.equals('elio_ff_filter_restrictions.id', this.blockListRestrictionId)
                    ]
                )
            );

            await this.filterRestrictionRepository.search(criteria, Shopware.Context.api)
                .then( (filterRestrictions) => {
                    filterRestrictions.forEach(function(filterRestriction) {
                        var filterIds = [];
                        filterRestriction.filters.forEach(
                            function(filter) {
                                filterIds.push(filter.id);
                            }
                        );
                        filterIds.forEach(
                            function(filterId) {
                                filterRestriction.filters.remove(filterId);
                            }
                        );
                        operator.filterRestrictionRepository.save(filterRestriction);
                    });
                });

            // Collecting all filters to save them
            if (this.allowListRestrictionId) {
                this.allowList.forEach(function (filter) {
                    var entity = operator.filterRestrictionFilterRepository.create(Shopware.Context.api);
                    entity.filterRestrictionId = operator.allowListRestrictionId;
                    entity.filterId = filter.id;
                    entities.push(entity);
                });
            }
            if (this.allowListRestrictionId) {
                this.blockList.forEach(function (filter) {
                    var entity = operator.filterRestrictionFilterRepository.create(Shopware.Context.api);
                    entity.filterRestrictionId = operator.blockListRestrictionId;
                    entity.filterId = filter.id;
                    entities.push(entity);
                });
            }

            // Saving new links into elio_ff_filter_restrictions_filters table
            await this.filterRestrictionFilterRepository.sync(entities, Shopware.Context.api, false)
                .finally(() => {this.isLoading = false;});
        },

        onSave() {
            console.log('ruler.onSave');
        }
    }
});