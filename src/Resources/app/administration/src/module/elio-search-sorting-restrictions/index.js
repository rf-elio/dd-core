import './page/elio-search-sorting-restrictions-index';
import './page/elio-search-sorting-restrictions-global';
import './page/elio-search-sorting-restrictions-search';
import './page/elio-search-sorting-restrictions-navigation';
import './page/elio-search-sorting-restrictions-customfilters';
import './page/elio-search-sorting-restrictions-customfilters-detail';
import './page/elio-search-sorting-restrictions-customfilters-create';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-search-sorting-restrictions-ruler');
})();

Shopware.Module.register('elio-search-sorting-restrictions', {
    type: 'plugin',
    name: 'ElioSearchSortingRestrictions',
    title: 'elio-search.restrictions.sorting.title',
    description: 'elio-search.restrictions.sorting.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'elio-search-sorting-restrictions-index',
            path: 'index',
            children: {
                global: {
                    component: 'elio-search-sorting-restrictions-global',
                    path: 'global',
                },
                search: {
                    component: 'elio-search-sorting-restrictions-search',
                    path: 'search',
                },
                navigation: {
                    component: 'elio-search-sorting-restrictions-navigation',
                    path: 'navigation',
                },
                customfilters: {
                    component: 'elio-search-sorting-restrictions-customfilters',
                    path: 'customfilters',
                }
            }
        },
        customFiltersDetail: {
            component: 'elio-search-sorting-restrictions-customfilters-detail',
            path: 'customfilter/detail/:id',
            props: {
                default: ($route) => {
                    return { customFilterId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.search.sorting.restrictions.index.customfilters'
            }
        },
        customFiltersCreate: {
            component: 'elio-search-sorting-restrictions-customfilters-create',
            path: 'customfilter/create',
            meta: {
                parentPath: 'elio.search.sorting.restrictions.index.customfilters'
            }
        }
    },

    navigation: [{
        label: 'elio-search.restrictions.sorting.title',
        color: '#014587',
        path: 'elio.search.sorting.restrictions.index.global',
        icon: 'regular-products',
        parent: 'elio-search',
        position: 1
    }]
});