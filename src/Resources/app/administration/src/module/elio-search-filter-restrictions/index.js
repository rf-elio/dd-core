import './page/elio-search-filter-restrictions-index';
import './page/elio-search-filter-restrictions-global';
import './page/elio-search-filter-restrictions-search';
import './page/elio-search-filter-restrictions-navigation';
import './page/elio-search-filter-restrictions-customfilters';
import './page/elio-search-filter-restrictions-customfilters-detail';
import './page/elio-search-filter-restrictions-customfilters-create';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-search-filter-restrictions-ruler');
})();

Shopware.Module.register('elio-search-filter-restrictions', {
    type: 'plugin',
    name: 'ElioSearchFilterRestrictions',
    title: 'elio-search.restrictions.filter.title',
    description: 'elio-search.restrictions.filter.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'elio-search-filter-restrictions-index',
            path: 'index',
            children: {
                global: {
                    component: 'elio-search-filter-restrictions-global',
                    path: 'global',
                },
                search: {
                    component: 'elio-search-filter-restrictions-search',
                    path: 'search',
                },
                navigation: {
                    component: 'elio-search-filter-restrictions-navigation',
                    path: 'navigation',
                },
                customfilters: {
                    component: 'elio-search-filter-restrictions-customfilters',
                    path: 'customfilters',
                }
            }
        },
        customFiltersDetail: {
            component: 'elio-search-filter-restrictions-customfilters-detail',
            path: 'customfilter/detail/:id',
            props: {
                default: ($route) => {
                    return { customFilterId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.search.filter.restrictions.index.customfilters'
            }
        },
        customFiltersCreate: {
            component: 'elio-search-filter-restrictions-customfilters-create',
            path: 'customfilter/create',
            meta: {
                parentPath: 'elio.search.filter.restrictions.index.customfilters'
            }
        }
    },

    navigation: [{
        label: 'elio-search.restrictions.filter.title',
        color: '#014587',
        path: 'elio.search.filter.restrictions.index.global',
        icon: 'regular-products',
        parent: 'elio-search',
        position: 1
    }]
});