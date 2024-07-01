import template from './elio-data-discovery-sort-positions-table.html.twig';
import './elio-data-discovery-sort-positions-table.scss';

const {Criteria} = Shopware.Data;
const {Mixin} = Shopware;

Shopware.Component.register('elio-data-discovery-sort-positions-table', {
    template: template,

    inject: [
        'repositoryFactory',
    ],

    props: {
        isCategory: {
            type: Boolean,
            required: true,
            default() {
                return false;
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

    mixins: [
        Mixin.getByName('notification')
    ],

    computed: {
        categoryRepository() {
            return this.repositoryFactory.create('category');
        },
        productSortingRepository() {
            return this.repositoryFactory.create('elio_data_discovery_product_sorting');
        },
        productSortingTreeRepository() {
            return this.repositoryFactory.create('elio_data_discovery_product_sorting_tree');
        },
        productColumns() {
            return [
                {
                    property: 'product.name',
                    label: this.$tc('sw-category.base.products.columnNameLabel'),
                    dataIndex: 'name',
                    sortable: false,
                },
                {
                    property: 'product.productNumber',
                    label: this.$tc('sw-product.list.columnProductNumber'),
                    dataIndex: 'productNumber',
                    sortable: false,
                },
                {
                    property: 'position',
                    label: this.$tc('elio-data-discovery.sort-positions.base.position'),
                    dataIndex: 'position',
                    sortable: false,
                },
            ];
        },
    },

    data() {
        return {
            isLoading: false,
            isModified: false,
            category: null,
            products: null,
            productsTree: null,
            categoryHasChildren: false,
        }
    },

    created() {
        this.loadCategory();
        this.loadProducts();
        this.loadProductsTree();
    },

    methods: {
        productSortingCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('product');
            criteria.addAssociation('category');
            criteria.addSorting(
                Criteria.sort('position', 'ASC')
            );

            return criteria;
        },
        syncProducts() {
            const initContainer = Shopware.Application.getContainer('init');
            const headers = {
                Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
            };
            const endpoint = `_action/elio-data-discovery-product-sorting/${this.categoryId}/sync-products`;
            initContainer.httpClient.get(endpoint, { headers }).then((response) => {
                const responseData = response.data;
                if (responseData && responseData.message) {
                    this.createNotificationInfo({
                        message: this.$tc(responseData.message),
                    });
                }
                this.loadProducts();
            });
        },
        loadCategory() {
            this.categoryRepository
                .get(this.categoryId, Shopware.Context.api)
                .then(result => {
                    this.category = result;
                    this.categoryHasChildren = (result.childCount > 0);
                })
        },
        loadProducts() {
            const criteria = this.productSortingCriteria();
            criteria.addFilter(
                Criteria.equals('elio_data_discovery_product_sorting.categoryId', this.categoryId)
            );

            this.productSortingRepository
                .search(criteria, Shopware.Context.api)
                .then(result => {
                    result.forEach((product, index) => {
                        product.position = index + 1;
                    })
                    this.products = result;
                });
        },
        loadProductsTree() {
            const criteria = this.productSortingCriteria();
            criteria.addFilter(
                Criteria.equals('elio_data_discovery_product_sorting_tree.categoryId', this.categoryId)
            );

            this.productSortingTreeRepository
                .search(criteria, Shopware.Context.api)
                .then(result => {
                    this.productsTree = result;
                });
        },
        sortDuplicatePositions(event, item) {
            let operator = this;
            const productIndexOld = this.products.findIndex(product => product.productId === item.productId);

            this.products.sort((a, b) => {
                return a.position - b.position;
            })
            const productIndex = this.products.findIndex(product => product.productId === item.productId);

            if (productIndexOld <= productIndex) {
                this.products.forEach((product, index) => {
                    if (index >= productIndexOld && index < productIndex) {
                        product.position--;
                    }
                    if ((index === productIndex && index < operator.products.length -1) && operator.products[index + 1].position === product.position) {
                        operator.products[index + 1].position--;
                    }
                    if (product.position > operator.products.length) {
                        product.position = operator.products.length;
                    }
                })
                this.products.sort((a, b) => {
                    return a.position - b.position;
                })
            }

            if (productIndex > 0) {
                if (this.products[productIndex].position === this.products[productIndex - 1].position) {
                    [this.products[productIndex - 1], this.products[productIndex]] = [this.products[productIndex], this.products[productIndex - 1]];
                }
            }

            let ind = [];
            this.products.forEach((product) => {
                let currentPositionNumber = product.position;
                while (ind[currentPositionNumber] === 1) {
                    currentPositionNumber++;
                }
                ind[currentPositionNumber] = 1;
                product.position = currentPositionNumber;
            });
        },
        recalculateSort() {
            const initContainer = Shopware.Application.getContainer('init');
            const headers = {
                Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
            };
            const endpoint = `_action/elio-data-discovery/recalculate-sort`;
            initContainer.httpClient.get(endpoint, { headers });
        },
        async saveAll() {
            this.isModified = false;
            this.isLoading = true;
            let operator = this;
            let entities = [];

            await this.products.forEach(function (product) {
                let entity = operator.productSortingRepository.create(Shopware.Context.api);
                entity.id = product.id;
                entity.productId = product.productId;
                entity.categoryId = product.categoryId;
                entity.position = Number(product.position);
                entities.push(entity);
            });

            await this.productSortingRepository.sync(entities, Shopware.Context.api, false)
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }
});
