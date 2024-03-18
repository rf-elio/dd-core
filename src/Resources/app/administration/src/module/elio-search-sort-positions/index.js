import './page/elio-data-discovery-sort-positions-index';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-data-discovery-sort-positions-ruler');
})();

Shopware.Module.register('elio-data-discovery-sort-positions', {
    type: 'plugin',
    name: 'ElioDataDiscoverySortPositions',
    title: 'elio-data-discovery.sort-positions.title',
    description: 'elio-data-discovery.sort-positions.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'elio-data-discovery-sort-positions-index',
            path: 'index',
        },
    },
});