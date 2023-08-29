import './page/elio-search-export-list';
import './page/elio-search-export-detail';
import './page/elio-search-export-create';
import ExportService from './service/export.service';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Service().register('elioSearchExport', () => {
    return new ExportService(
      Shopware.Application.getContainer('init').httpClient,
      Shopware.Service('loginService'),
    );
});

Shopware.Module.register('elio-search-export', {
    type: 'plugin',
    name: 'ElioSearchExport',
    title: 'elio-search-export.title',
    description: 'elio-search-export.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        list: {
            component: 'elio-search-export-list',
            path: 'list',
        },
        detail: {
            component: 'elio-search-export-detail',
            path: 'detail/:id',
            props: {
                default: ($route) => {
                    return { exportId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.search.export.list'
            }
        },
        create: {
            component: 'elio-search-export-create',
            path: 'create',
            meta: {
                parentPath: 'elio.search.export.list'
            }
        }
    },

    navigation: [{
        label: 'elio-search-export.title',
        color: '#014587',
        path: 'elio.search.export.list',
        icon: 'regular-products',
        parent: 'elio-search',
        position: 1
    }]
});