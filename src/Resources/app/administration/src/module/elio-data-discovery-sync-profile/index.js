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
                parentPath: 'elio.data.discovery.sync.profile.list'
            }
        },
        create: {
            component: 'elio-data-discovery-sync-profile-create',
            path: 'create',
            meta: {
                parentPath: 'elio.data.discovery.sync.profile.list'
            }
        }
    }
});

const httpClient = Shopware.Service('syncService').httpClient;
const url = '/_action/elio-data-discovery/features/syncProfile';
const basicHeaders = {
    Authorization: `Bearer ${Shopware.Context.api.authToken.access}`,
    'Content-Type': 'application/json'
};
httpClient.get(url, {headers: basicHeaders}).then((response) => {
    if (response.data.enabled) {
        Shopware.Module.register('elio-data-discovery-sync-profile-menu', {
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
                            return {exportId: $route.params.id};
                        }
                    },
                    meta: {
                        parentPath: 'elio.data.discovery.sync.profile.list'
                    }
                },
                create: {
                    component: 'elio-data-discovery-sync-profile-create',
                    path: 'create',
                    meta: {
                        parentPath: 'elio.data.discovery.sync.profile.list'
                    }
                }
            },

            settingsItem: [{
                to: 'elio.data.discovery.sync.profile.list',
                group: 'plugins',
                icon: 'regular-products',
                iconComponent: 'elio-data-discovery-plugin-icon',
                id: '',
                name: '',
                label: 'elio-data-discovery-sync-profile.title',
                color: '#014587'
            }]
        });
    }
});