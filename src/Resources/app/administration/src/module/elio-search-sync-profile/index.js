import './page/elio-search-sync-profile-list';
import './page/elio-search-sync-profile-detail';
import './page/elio-search-sync-profile-create';
import SyncProfileService from './service/sync-profile.service';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Service().register('elioSearchSyncProfile', () => {
    return new SyncProfileService(
      Shopware.Application.getContainer('init').httpClient,
      Shopware.Service('loginService'),
    );
});

Shopware.Module.register('elio-search-sync-profile', {
    type: 'plugin',
    name: 'ElioSyncProfile',
    title: 'elio-search-sync-profile.title',
    description: 'elio-search-sync-profile.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        list: {
            component: 'elio-search-sync-profile-list',
            path: 'list',
        },
        detail: {
            component: 'elio-search-sync-profile-detail',
            path: 'detail/:id',
            props: {
                default: ($route) => {
                    return { exportId: $route.params.id };
                }
            },
            meta: {
                parentPath: 'elio.search.sync.profile.list'
            }
        },
        create: {
            component: 'elio-search-sync-profile-create',
            path: 'create',
            meta: {
                parentPath: 'elio.search.sync.profile.list'
            }
        }
    },

    navigation: [{
        label: 'elio-search-sync-profile.title',
        color: '#014587',
        path: 'elio.search.sync.profile.list',
        icon: 'regular-products',
        parent: 'elio-search',
        position: 1
    }]
});