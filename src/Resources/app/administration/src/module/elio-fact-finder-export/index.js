import './page/ff-export-list';
import './page/ff-export-detail';
import './page/ff-export-create';

Shopware.Module.register('elio-factfinder-export', {
    type: 'plugin',
    name: 'FACTFinderExport',
    title: 'ff.export.title',
    description: 'ff.export.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        list: {
            component: 'ff-export-list',
            path: 'list',
        },
        detail: {
            component: 'ff-export-detail',
            path: 'detail/:id',
            props: {
                default: ($route) => {
                    return { exportId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.factfinder.export.list'
            }
        },
        create: {
            component: 'ff-export-create',
            path: 'create',
            meta: {
                parentPath: 'elio.factfinder.export.list'
            }
        }
    },

    navigation: [{
        label: 'ff.export.title',
        color: '#014587',
        path: 'elio.factfinder.export.list',
        icon: 'default-shopping-paper-bag-product',
        parent: 'elio-fact-finder',
        position: 1
    }]
});