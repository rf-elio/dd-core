import './page/elio-data-discovery-sorting-restrictions-index';
import './page/elio-data-discovery-sorting-restrictions-global';
import './page/elio-data-discovery-sorting-restrictions-search';
import './page/elio-data-discovery-sorting-restrictions-navigation';
import './page/elio-data-discovery-sorting-restrictions-customfilters';
import './page/elio-data-discovery-sorting-restrictions-customfilters-detail';
import './page/elio-data-discovery-sorting-restrictions-customfilters-create';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-data-discovery-sorting-restrictions-ruler');
})();

Shopware.Module.register('elio-data-discovery-sorting-restrictions', {
    type: 'plugin',
    name: 'ElioDataDiscoverySortingRestrictions',
    title: 'elio-data-discovery.restrictions.sorting.title',
    description: 'elio-data-discovery.restrictions.sorting.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'elio-data-discovery-sorting-restrictions-index',
            path: 'index',
            children: {
                global: {
                    component: 'elio-data-discovery-sorting-restrictions-global',
                    path: 'global',
                },
                search: {
                    component: 'elio-data-discovery-sorting-restrictions-search',
                    path: 'search',
                },
                navigation: {
                    component: 'elio-data-discovery-sorting-restrictions-navigation',
                    path: 'navigation',
                },
                customfilters: {
                    component: 'elio-data-discovery-sorting-restrictions-customfilters',
                    path: 'customfilters',
                }
            }
        },
        customFiltersDetail: {
            component: 'elio-data-discovery-sorting-restrictions-customfilters-detail',
            path: 'customfilter/detail/:id',
            props: {
                default: ($route) => {
                    return { customFilterId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.data.discovery.sorting.restrictions.index.customfilters'
            }
        },
        customFiltersCreate: {
            component: 'elio-data-discovery-sorting-restrictions-customfilters-create',
            path: 'customfilter/create',
            meta: {
                parentPath: 'elio.data.discovery.sorting.restrictions.index.customfilters'
            }
        }
    },

    navigation: [{
        label: 'elio-data-discovery.restrictions.sorting.title',
        color: '#014587',
        path: 'elio.data.discovery.sorting.restrictions.index.global',
        icon: 'regular-products',
        parent: 'elio-data-discovery',
        position: 1
    }]
});