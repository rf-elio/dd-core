import './page/elio-search-restrictions-index';
import './page/elio-search-restrictions-global';
import './page/elio-search-restrictions-search';
import './page/elio-search-restrictions-navigation';
import './page/elio-search-restrictions-customfilters';
import './page/elio-search-restrictions-customfilters-detail';
import './page/elio-search-restrictions-customfilters-create';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-search-restrictions-ruler');
})();

Shopware.Module.register('elio-search-restrictions', {
    type: 'plugin',
    name: 'ElioSearchRestrictions',
    title: 'elio-search.restrictions.title',
    description: 'elio-search.restrictions.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'elio-search-restrictions-index',
            path: 'index',
            children: {
                global: {
                    component: 'elio-search-restrictions-global',
                    path: 'global',
                },
                search: {
                    component: 'elio-search-restrictions-search',
                    path: 'search',
                },
                navigation: {
                    component: 'elio-search-restrictions-navigation',
                    path: 'navigation',
                },
                customfilters: {
                    component: 'elio-search-restrictions-customfilters',
                    path: 'customfilters',
                }
            }
        },
        customFiltersDetail: {
            component: 'elio-search-restrictions-customfilters-detail',
            path: 'customfilter/detail/:id',
            props: {
                default: ($route) => {
                    return { customFilterId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.search.restrictions.index.customfilters'
            }
        },
        customFiltersCreate: {
            component: 'elio-search-restrictions-customfilters-create',
            path: 'customfilter/create',
            meta: {
                parentPath: 'elio.search.restrictions.index.customfilters'
            }
        }
    },

    navigation: [{
        label: 'elio-search.restrictions.title',
        color: '#014587',
        path: 'elio.search.restrictions.index.global',
        icon: 'regular-products',
        parent: 'elio-search',
        position: 1
    }]
});