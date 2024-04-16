import LoggingService from "./service/logging.service";

import './component/elio-data-discovery-modal-delete-log'
import './page/elio-data-discovery-logging-list';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Service().register('elioDataDiscoveryLogging', () => {
    return new LoggingService(
        Shopware.Application.getContainer('init').httpClient,
        Shopware.Service('loginService'),
    );
});

Shopware.Module.register('elio-data-discovery-logging', {
    type: 'plugin',
    name: 'ElioDataDiscoveryLogging',
    title: 'elio-data-discovery-logging.title',
    description: 'elio-data-discovery-logging.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        list: {
            component: 'elio-data-discovery-logging-list',
            path: 'list',
        }
    },

    settingsItem: [{
        to: 'elio.data.discovery.logging.list',
        group: 'plugins',
        icon: 'regular-products',
        iconComponent: 'elio-data-discovery-plugin-icon',
        id: '',
        name: '',
        label: 'elio-data-discovery-logging.title',
        color: '#014587'
    }]
});
