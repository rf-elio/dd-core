import './module/elio-extension-config-detail/index';
import './module/elio-data-discovery-sync-profile/index';
import './module/elio-data-discovery-logging/index';
import './module/elio-data-discovery-commands/index';
import './module/elio-data-discovery-filter-restrictions/index';
import './module/elio-data-discovery-sorting-restrictions/index';
import './module/elio-data-discovery-sort-positions/index';
import './module/sw-category-detail-override/index';
import './module/sw-category-view-override/index';
import './view/sw-category-detail-filter-ruler/index';
import './view/sw-category-detail-sorting-ruler/index';
import './view/sw-category-detail-positions-ruler/index';
import './module/sw-cms/blocks/commerce/edd-advisor-campaign';
import './module/sw-cms/elements/advisor-campaign';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-data-discovery-plugin-icon');
})();

Shopware.Module.register('sw-category-tab-ruler', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.category.detail') {
            currentRoute.children.push({
                name: 'sw.category.detail.filter.ruler',
                path: 'filter-ruler',
                component: 'elio-category-detail-filter-ruler',
                meta: {
                    parentPath: 'sw.category.index',
                    privilege: 'category.viewer'
                }
            });
            currentRoute.children.push({
                name: 'sw.category.detail.sorting.ruler',
                path: 'sorting-ruler',
                component: 'elio-category-detail-sorting-ruler',
                meta: {
                    parentPath: 'sw.category.index',
                    privilege: 'category.viewer'
                }
            });
            currentRoute.children.push({
                name: 'sw.category.detail.positions.ruler',
                path: 'positions-ruler',
                component: 'elio-category-detail-positions-ruler',
                meta: {
                    parentPath: 'sw.category.index',
                    privilege: 'category.viewer'
                }
            });
        }
        next(currentRoute);
    }
});
