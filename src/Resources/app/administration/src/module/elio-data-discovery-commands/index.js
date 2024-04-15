import './page/elio-data-discovery-commands-index';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';
Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Module.register('elio-data-discovery-commands', {
    type: 'plugin',
    name: 'ElioDataDiscoveryCommands',
    title: 'elio-data-discovery-commands.title',
    description: 'elio-data-discovery-commands.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'elio-data-discovery-commands-index',
            path: 'index',
        }
    },

    settingsItem: [{
        to: 'elio.data.discovery.commands.index',
        group: 'plugins',
        icon: 'regular-products',
        iconComponent: 'elio-data-discovery-plugin-icon',
        id: '',
        name: '',
        label: 'elio-data-discovery-commands.title',
        color: '#014587'
    }]
});
