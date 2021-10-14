import './page/ff-restrictions-index';
import './page/ff-restrictions-global';
import './page/ff-restrictions-search';
import './page/ff-restrictions-navigation';
import './page/ff-restrictions-customfilters';
import './page/ff-restrictions-customfilters-detail';
import './page/ff-restrictions-customfilters-create';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-fact-finder-restrictions-ruler');
})();

Shopware.Module.register('elio-factfinder-restrictions', {
    type: 'plugin',
    name: 'FACTFinderRestrictions',
    title: 'ff.restrictions.title',
    description: 'ff.restrictions.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'ff-restrictions-index',
            path: 'index',
            children: {
                global: {
                    component: 'ff-restrictions-global',
                    path: 'global',
                },
                search: {
                    component: 'ff-restrictions-search',
                    path: 'search',
                },
                navigation: {
                    component: 'ff-restrictions-navigation',
                    path: 'navigation',
                },
                customfilters: {
                    component: 'ff-restrictions-customfilters',
                    path: 'customfilters',
                }
            }
        },
        customFiltersDetail: {
            component: 'ff-restrictions-customfilters-detail',
            path: 'customfilter/detail/:id',
            props: {
                default: ($route) => {
                    return { customFilterId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.factfinder.restrictions.index.customfilters'
            }
        },
        customFiltersCreate: {
            component: 'ff-restrictions-customfilters-create',
            path: 'customfilter/create',
            meta: {
                parentPath: 'elio.factfinder.restrictions.index.customfilters'
            }
        }
    },

    navigation: [{
        label: 'ff.restrictions.title',
        color: '#014587',
        path: 'elio.factfinder.restrictions.index.global',
        icon: 'default-shopping-paper-bag-product',
        parent: 'elio-fact-finder',
        position: 1
    }]
});