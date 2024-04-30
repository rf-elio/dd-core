import template from './elio-data-discovery-restriction-ruler.html.twig';
import './elio-data-discovery-restriction-ruler.scss';

const {Criteria} = Shopware.Data;

Shopware.Component.register('elio-data-discovery-restriction-ruler', {
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
        rulerType: {
            type: String,
            required: false,
            default() {
                return 'filter';
            }
        }
    },

    computed: {
        filterRepository() {
            return this.repositoryFactory.create('elio_data_discovery_filter');
        },
        filterRestrictionRepository() {
            return this.repositoryFactory.create('elio_data_discovery_filter_restrictions');
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
                this.$router.push({name: destination.name, params: destination.params});
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
            if (this.isInherited) {
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
            if (this.allowListRestrictionId !== '') {
                this.isModified = true;
                this.allowAllChecked = true;
                this.blockAllChecked = false;
            }
        },

        onAllowSelectedClicked() {
            if (this.allowListRestrictionId !== '') {
                this.isModified = true;
                this.allowAllChecked = false;
            }
        },

        onBlockAllClicked() {
            if (this.blockListRestrictionId !== '') {
                this.isModified = true;
                this.blockAllChecked = true;
                this.allowAllChecked = false;
            }
        },

        onBlockSelectedClicked() {
            if (this.blockListRestrictionId !== '') {
                this.isModified = true;
                this.blockAllChecked = false;
            }
        },

        onCreated() {
            const that = this;
            if (this.salesChannelId == null) {
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
                var criteria = this.getFilterRestrictionLoadCriteria();
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

                        // Creating filterRestrictionsEntities if there is no-one into elio_data_discovery_filter_restrictions
                        if (!isAllowColumnPresent) {
                            this.createFilterRestriction(false);
                        }
                        if (!isBlockColumnPresent) {
                            this.createFilterRestriction(true);
                        }
                    });

                await this.buildFilters();
                this.isLoading = false;
            } catch {
                this.isLoading = false;
                this.$router.push({name: 'elio.data.discovery.' + this.rulerType + '.restrictions.index.global'});
            }
        },

        async buildFilters() {
            var criteria = new Criteria();
            criteria.setLimit(this.limitForCriteria); /* upddate it */
            criteria.addFilter(Criteria.equals('type', this.rulerType))
            if (this.movedFiltersIds.length > 0) {
                criteria.addFilter(Criteria.not(
                    'AND',
                    [
                        Criteria.equalsAny('elio_data_discovery_filter.id', this.movedFiltersIds)
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

            // Removing old links from elio_data_discovery_filter_restrictions_filters table
            var criteria = new Criteria();
            criteria.addAssociation('filters');
            criteria.addFilter(
                Criteria.multi(
                    'OR',
                    [
                        Criteria.equals('elio_data_discovery_filter_restrictions.id', this.allowListRestrictionId),
                        Criteria.equals('elio_data_discovery_filter_restrictions.id', this.blockListRestrictionId)
                    ]
                )
            );

            await this.filterRestrictionRepository.search(criteria, Shopware.Context.api)
                .then((filterRestrictions) => {
                    filterRestrictions.forEach(function (filterRestriction) {
                        filterRestriction.isAllChecked = (filterRestriction.isAllowed) ? operator.allowAllChecked : operator.blockAllChecked;
                        filterRestriction.isInherited = operator.isInherited;
                        operator.syncFiltersToFilterRestriction(filterRestriction);
                    });
                });
            this.isLoading = false;
        },

        syncFiltersToFilterRestriction(filterRestriction) {
            // remove all
            var filtersIds = [];
            filterRestriction.filters.forEach(filter => {
                filtersIds.push(filter.id);
            });
            filtersIds.forEach(id => {
                filterRestriction.filters.remove(id);
            });

            // add from list
            (filterRestriction.isAllowed ? this.allowList : this.blockList).forEach(filter => {
                filterRestriction.filters.add(filter);
            });

            // save
            this.filterRestrictionRepository.save(filterRestriction);
        },

        createFilterRestriction(isForBlock = false) {
            var filterRestriction = this.filterRestrictionRepository.create(Shopware.Context.api);

            filterRestriction.isCategory = this.isCategory;
            if (!this.isCategory) {
                filterRestriction.layer = this.layer;
            } else {
                filterRestriction.layer = this.layer;
                filterRestriction.categoryId = this.categoryId;
            }
            filterRestriction.isAllowed = !isForBlock;
            filterRestriction.isAllChecked = !isForBlock;
            filterRestriction.salesChannelId = this.salesChannelId;
            filterRestriction.languageId = this.languageId;
            filterRestriction.isInherited = this.salesChannelId != null;
            this.isInherited = this.salesChannelId != null;

            this.filterRestrictionRepository
                .save(filterRestriction, Shopware.Context.api)
                .then((response) => {
                    var id = JSON.parse(response.config.data).id;
                    if (id) {
                        if (!isForBlock) {
                            this.allowListRestrictionId = id;
                        } else {
                            this.blockListRestrictionId = id;
                        }
                    }
                });
        },

        getFilterRestrictionLoadCriteria() {
            var criteria = new Criteria();
            criteria.addAssociation('filters')
            var criteriaConditions = [
                Criteria.equals('elio_data_discovery_filter_restrictions.salesChannelId', this.salesChannelId),
                Criteria.equals('elio_data_discovery_filter_restrictions.languageId', this.languageId),
                Criteria.equals('elio_data_discovery_filter_restrictions.layer', this.layer)
            ];
            if (this.isCategory && this.categoryId != null) {
                criteriaConditions.push(Criteria.equals('elio_data_discovery_filter_restrictions.isCategory', true));
                criteriaConditions.push(Criteria.equals('elio_data_discovery_filter_restrictions.categoryId', this.categoryId));
            } else {
                criteriaConditions.push(Criteria.equals('elio_data_discovery_filter_restrictions.isCategory', false));
            }
            criteria.addFilter(
                Criteria.multi(
                    'AND',
                    criteriaConditions
                )
            );
            return criteria;
        },
    }
});
