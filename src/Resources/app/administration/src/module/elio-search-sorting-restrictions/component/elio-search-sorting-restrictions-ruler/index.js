import template from './elio-search-sorting-restriction-ruler.html.twig';
import './elio-search-sorting-restriction-ruller.scss';

const {Criteria} = Shopware.Data;

Shopware.Component.register('elio-search-sorting-restriction-ruler', {
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
        rulerHeader: {
            type: String,
            required: false,
            default() {
                return '';
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
            return this.repositoryFactory.create('elio_search_filter');
        },
        filterRestrictionRepository() {
            return this.repositoryFactory.create('elio_search_filter_restrictions');
        },
        filterRestrictionFilterRepository() {
            return this.repositoryFactory.create('elio_search_filter_restrictions_filters');
        },
        languageRepository() {
            return this.repositoryFactory.create('language');
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
            limitForCriteria: 500,
            salesChannelId: null,
            languageId: null,
            languageIdsList: [{
                id: null,
                name: 'All languages'
            }],
            isModified: false,
            isDisplayingLeavePageWarning: false,
            forceDiscardChanges: false,
            nextRoute: null,
            isInherited: true,
            isInheritable: false
        }
    },

    created() {
        this.onCreated();
    },

    watch: {
        salesChannelId() {
            this.isInheritable = this.salesChannelId != null || this.languageId != null;
            this.loadFilters();
        },
        languageId() {
            this.isInheritable = this.salesChannelId != null || this.languageId != null;
            this.loadFilters();
        }
    },

    methods: {
        setSalesChannelId(salesChannelId) {
            this.salesChannelId = salesChannelId;
        },

        onLeaving(to) {
            if (this.forceDiscardChanges) {
                this.forceDiscardChanges = false;
                return true;
            }
            if (this.isModified) {
                this.isDisplayingLeavePageWarning = true;
                this.nextRoute = to;
                return false;
            } else {
                return true;
            }
        },

        onLeaveModalClose() {
            this.nextRoute = null;
            this.isDisplayingLeavePageWarning = false;
        },

        onLeaveModalConfirm(destination) {
            this.forceDiscardChanges = true;
            this.isDisplayingLeavePageWarning = false;

            this.$nextTick(() => {
                this.$router.push({ name: destination.name, params: destination.params });
            });
        },

        restoreInheritance() {
            this.isModified = true;
            this.isInherited = true;
        },

        removeInheritance() {
            this.isModified = true;
            this.isInherited = false;
        },

        onDragStart(dragData) {
            if (dragData.target.className === "filter") {
                this.currentDragItem = dragData.target;
            }
        },

        onDrop(dragData) {
            if(this.isInherited) {
                return;
            }
            if (dragData.target.classList.contains("ruler-tab-filter-list") || dragData.target.classList.contains("filter")) {
                dragData.preventDefault();
                this.isModified = true;

                var realTarget = dragData.target;
                if (dragData.target.classList.contains("filter")) {
                    realTarget = dragData.target.closest('.ruler-tab-filter-list');
                }

                var operator = this;
                var draggedId = this.currentDragItem.getAttribute("data-filter-id");
                var targetColumnType = realTarget.getAttribute("data-filter-column");
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
                this.isModified = true;
                this.allowAllChecked = true;
                this.blockAllChecked = false;
            }
        },

        onAllowSelectedClicked() {
            if(this.allowListRestrictionId !== '') {
                this.isModified = true;
                this.allowAllChecked = false;
            }
        },

        onBlockAllClicked() {
            if(this.blockListRestrictionId !== '') {
                this.isModified = true;
                this.blockAllChecked = true;
                this.allowAllChecked = false;
            }
        },

        onBlockSelectedClicked() {
            if(this.blockListRestrictionId !== '') {
                this.isModified = true;
                this.blockAllChecked = false;
            }
        },

        onCreated() {
            const that = this;
            if(this.salesChannelId == null) {
                this.isInherited = false;
            }
            this.languageRepository.search(new Criteria, Shopware.Context.api).then((languages) => {
                languages.forEach((language) => {
                    that.languageIdsList.push({
                        id: language.id,
                        name: language.name
                    });
                });
            });
            this.loadFilters();
        },

        //todo: place below functions to seperate API/service

        async loadFilters() {
            this.isModified = false;
            this.allList = [];
            this.allowList = [];
            this.blockList = [];
            this.currentDragItem = null;
            this.allowAllChecked = true;
            this.blockAllChecked = false;

            this.isLoading = true;
            try {
                var criteria = new Criteria();
                criteria.addAssociation('filters')
                criteria.addFilter(Criteria.equals('filters.type', 'sorting'))
                if (this.isCategory && this.categoryId != null) {
                    criteria.addFilter(
                        Criteria.multi(
                            'AND',
                            [
                                Criteria.equals('elio_search_filter_restrictions.isCategory', true),
                                Criteria.equals('elio_search_filter_restrictions.categoryId', this.categoryId),
                                Criteria.equals('elio_search_filter_restrictions.salesChannelId', this.salesChannelId),
                                Criteria.equals('elio_search_filter_restrictions.languageId', this.languageId)
                            ]
                        )
                    );
                } else {
                    criteria.addFilter(
                        Criteria.multi(
                            'AND',
                            [
                                Criteria.equals('elio_search_filter_restrictions.isCategory', false),
                                Criteria.equals('elio_search_filter_restrictions.layer', this.layer),
                                Criteria.equals('elio_search_filter_restrictions.salesChannelId', this.salesChannelId),
                                Criteria.equals('elio_search_filter_restrictions.languageId', this.languageId)
                            ]
                        )
                    );
                }

                var isAllowColumnPresent = false;
                var isBlockColumnPresent = false;
                this.movedFiltersIds = [];
                var operator = this;
                await this.filterRestrictionRepository
                    .search(criteria, Shopware.Context.api)
                    .then(filterRestrictions => {
                        filterRestrictions.forEach(function (restrictionColumn) {
                            if (restrictionColumn.isAllowed) {
                                operator.allowListRestrictionId = restrictionColumn.id;
                                operator.allowAllChecked = restrictionColumn.isAllChecked;
                                operator.isInherited = restrictionColumn.isInherited
                                isAllowColumnPresent = true;
                            } else {
                                operator.blockListRestrictionId = restrictionColumn.id;
                                operator.blockAllChecked = restrictionColumn.isAllChecked;
                                operator.isInherited = restrictionColumn.isInherited
                                isBlockColumnPresent = true;
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

                        // Creating filterRestrictionsEntities if there is no-one into elio_search_filter_restrictions
                        if (!isAllowColumnPresent) {
                            var filterRestriction = this.filterRestrictionRepository.create(Shopware.Context.api);

                            filterRestriction.isCategory = this.isCategory;
                            if (!this.isCategory) {
                                filterRestriction.layer = this.layer;
                            } else {
                                filterRestriction.categoryId = this.categoryId;
                            }
                            filterRestriction.isAllowed = true;
                            filterRestriction.isAllChecked = true;
                            filterRestriction.salesChannelId = operator.salesChannelId;
                            filterRestriction.languageId = operator.languageId;
                            filterRestriction.isInherited = operator.salesChannelId != null;
                            operator.isInherited = operator.salesChannelId != null;

                            this.filterRestrictionRepository.save(filterRestriction, Shopware.Context.api)
                                .then((response) => {
                                    var id = JSON.parse(response.config.data).id;
                                    if (id) {
                                        operator.allowListRestrictionId = id;
                                    }
                                });
                        }
                        if (!isBlockColumnPresent) {
                            filterRestriction = this.filterRestrictionRepository.create(Shopware.Context.api);

                            filterRestriction.isCategory = this.isCategory;
                            if (!this.isCategory) {
                                filterRestriction.layer = this.layer;
                            } else {
                                filterRestriction.categoryId = this.categoryId;
                            }
                            filterRestriction.isAllowed = false;
                            filterRestriction.isAllChecked = false;
                            filterRestriction.salesChannelId = operator.salesChannelId;
                            filterRestriction.languageId = operator.languageId;
                            filterRestriction.isInherited = operator.salesChannelId != null;
                            operator.isInherited = operator.salesChannelId != null;

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
                this.$router.push({name: 'elio.search.sorting.restrictions.index.global'});
            }
        },

        async buildFilters() {
            var criteria = new Criteria();
            criteria.addFilter(Criteria.equals('type', 'sorting'));
            criteria.setLimit(this.limitForCriteria); /* upddate it */
            if (this.movedFiltersIds.length > 0) {
                criteria.addFilter(Criteria.not(
                    'AND',
                    [
                        Criteria.equalsAny('elio_search_filter.id', this.movedFiltersIds)
                    ]
                ));
            }
            var operator = this;
            await this.filterRepository
                .search(criteria, Shopware.Context.api)
                .then((filters) => {
                    filters.forEach(function (filter) {
                        operator.allList.push(filter);
                    });
                });
        },

        async saveAll() {
            this.isModified = false;
            this.isLoading = true;
            var operator = this;
            var entities = [];

            // Removing old links from elio_search_filter_restrictions_filters table
            var criteria = new Criteria();
            criteria.addAssociation('filters');
            criteria.addFilter(Criteria.equals('filters.type', 'sorting'));
            criteria.addFilter(
                Criteria.multi(
                    'OR',
                    [
                        Criteria.equals('elio_search_filter_restrictions.id', this.allowListRestrictionId),
                        Criteria.equals('elio_search_filter_restrictions.id', this.blockListRestrictionId)
                    ]
                )
            );

            await this.filterRestrictionRepository.search(criteria, Shopware.Context.api)
                .then( (filterRestrictions) => {
                    filterRestrictions.forEach(function(filterRestriction) {

                        filterRestriction.isAllChecked = (filterRestriction.isAllowed) ? operator.allowAllChecked : operator.blockAllChecked;
                        filterRestriction.isInherited = operator.isInherited;

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

            // Saving new links into elio_search_filter_restrictions_filters table
            await this.filterRestrictionFilterRepository.sync(entities, Shopware.Context.api, false)
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }
});
