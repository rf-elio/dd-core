import LoggingService from "./service/logging.service";

import './page/ff-logging-list';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Service().register('ffLogging', () => {
    return new LoggingService(
        Shopware.Application.getContainer('init').httpClient,
        Shopware.Service('loginService'),
    );
});

Shopware.Module.register('elio-factfinder-logging', {
    type: 'plugin',
    name: 'FACTFinderLogging',
    title: 'ff-logging.title',
    description: 'ff-logging.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        list: {
            component: 'ff-logging-list',
            path: 'list',
        }
    },

    navigation: [{
        label: 'ff-logging.title',
        color: '#014587',
        path: 'elio.factfinder.logging.list',
        icon: 'default-shopping-paper-bag-product',
        parent: 'elio-fact-finder',
        position: 3
    }]
});
