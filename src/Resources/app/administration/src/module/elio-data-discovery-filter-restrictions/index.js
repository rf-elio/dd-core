import './page/elio-data-discovery-filter-restrictions-index';
import './page/elio-data-discovery-filter-restrictions-global';
import './page/elio-data-discovery-filter-restrictions-search';
import './page/elio-data-discovery-filter-restrictions-navigation';
import './page/elio-data-discovery-filter-restrictions-customfilters';
import './page/elio-data-discovery-filter-restrictions-customfilters-detail';
import './page/elio-data-discovery-filter-restrictions-customfilters-create';

Shopware.Module.register('elio-data-discovery-filter-restrictions', {
    type: 'plugin',
    name: 'ElioDataDiscoveryFilterRestrictions',
    title: 'elio-data-discovery.restrictions.filter.title',
    description: 'elio-data-discovery.restrictions.filter.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'elio-data-discovery-filter-restrictions-index',
            path: 'index',
            children: {
                global: {
                    component: 'elio-data-discovery-filter-restrictions-global',
                    path: 'global',
                },
                search: {
                    component: 'elio-data-discovery-filter-restrictions-search',
                    path: 'search',
                },
                navigation: {
                    component: 'elio-data-discovery-filter-restrictions-navigation',
                    path: 'navigation',
                },
                customfilters: {
                    component: 'elio-data-discovery-filter-restrictions-customfilters',
                    path: 'customfilters',
                }
            }
        },
        customFiltersDetail: {
            component: 'elio-data-discovery-filter-restrictions-customfilters-detail',
            path: 'customfilter/detail/:id',
            props: {
                default: ($route) => {
                    return { customFilterId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.data.discovery.filter.restrictions.index.customfilters'
            }
        },
        customFiltersCreate: {
            component: 'elio-data-discovery-filter-restrictions-customfilters-create',
            path: 'customfilter/create',
            meta: {
                parentPath: 'elio.data.discovery.filter.restrictions.index.customfilters'
            }
        }
    },

    settingsItem: [{
        to: 'elio.data.discovery.filter.restrictions.index.global',
        group: 'plugins',
        icon: 'regular-products',
        iconComponent: 'elio-data-discovery-plugin-icon',
        id: '',
        name: '',
        label: 'elio-data-discovery.restrictions.filter.title',
        color: '#014587'
    }]
});