import './module/elio-fact-finder/index';
import './module/elio-fact-finder-restrictions/index';
import './module/sw-category-view-override/index';
import './view/sw-category-detail-ruler/index';
import './module/sw-category-detail-override/index';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';
Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Module.register('sw-category-tab-ruler', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.category.detail') {
            currentRoute.children.push({
                name: 'sw.category.detail.ruler',
                path: 'ruler',
                component: 'elio-category-detail-ruler',
                meta: {
                    parentPath: 'sw.category.index',
                    privilege: 'category.viewer'
                }
            });
        }
        next(currentRoute);
    }
});