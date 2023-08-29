import LoggingService from "./service/logging.service";

import './component/elio-search-modal-delete-log'
import './page/elio-search-logging-list';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Service().register('elioSearchLogging', () => {
    return new LoggingService(
        Shopware.Application.getContainer('init').httpClient,
        Shopware.Service('loginService'),
    );
});

Shopware.Module.register('elio-search-logging', {
    type: 'plugin',
    name: 'ElioSearchLogging',
    title: 'elio-search-logging.title',
    description: 'elio-search-logging.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        list: {
            component: 'elio-search-logging-list',
            path: 'list',
        }
    },

    navigation: [{
        label: 'elio-search-logging.title',
        color: '#014587',
        path: 'elio.search.logging.list',
        icon: 'regular-products',
        parent: 'elio-search',
        position: 3
    }]
});
