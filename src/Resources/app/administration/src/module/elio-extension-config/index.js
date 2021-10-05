import './page/elio-extension-config-detail';
import './page/elio-extension-config-index';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-extension-card-base');
})();

Shopware.Module.register('elio-extension-config', {
    type: 'plugin',
    name: 'FACTFinderRestrictions',
    title: 'title',
    description: 'description',
    color: '#189EFF',
    icon: 'default-object-plug',

    routes: {
        index: {
            component: 'elio-plugin-config-index',
            path: 'extensions',
            propsData: {
                isTheme: false
            }
        },
        detail: {
            component: 'elio-plugin-config-detail',
            path: 'extensions/detail/:namespace',
            props: {
                default: ($route) => {
                    return { namespace: $route.params.namespace };
                }
            },
            meta: {
                parentPath: 'elio.extension.config.index'
            }
        },
    },

    navigation: [{
        label: 'navLabel',
        color: '#189EFF',
        path: 'elio.extension.config.index',
        icon: 'default-shopping-paper-bag-product',
        parent: 'sw-extension',
        position: 90
    }]
});