import './page/elio-data-discovery-sync-profile-list';
import './page/elio-data-discovery-sync-profile-detail';
import './page/elio-data-discovery-sync-profile-create';
import SyncProfileService from './service/sync-profile.service';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Service().register('elioDataDiscoverySyncProfile', () => {
    return new SyncProfileService(
      Shopware.Application.getContainer('init').httpClient,
      Shopware.Service('loginService'),
    );
});

Shopware.Module.register('elio-data-discovery-sync-profile', {
    type: 'plugin',
    name: 'ElioSyncProfile',
    title: 'elio-data-discovery-sync-profile.title',
    description: 'elio-data-discovery-sync-profile.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        list: {
            component: 'elio-data-discovery-sync-profile-list',
            path: 'list',
        },
        detail: {
            component: 'elio-data-discovery-sync-profile-detail',
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
            component: 'elio-data-discovery-sync-profile-create',
            path: 'create',
            meta: {
                parentPath: 'elio.search.sync.profile.list'
            }
        }
    },

    navigation: [{
        label: 'elio-data-discovery-sync-profile.title',
        color: '#014587',
        path: 'elio.search.sync.profile.list',
        icon: 'regular-products',
        parent: 'elio-data-discovery',
        position: 1
    }]
});