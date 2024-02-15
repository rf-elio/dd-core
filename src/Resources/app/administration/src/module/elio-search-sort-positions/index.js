import './page/elio-search-sort-positions-index';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-search-sort-positions-ruler');
})();

Shopware.Module.register('elio-search-sort-positions', {
    type: 'plugin',
    name: 'ElioSearchSortPositions',
    title: 'elio-search.sort-positions.title',
    description: 'elio-search.sort-positions.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'elio-search-sort-positions-index',
            path: 'index',
        },
    },
});