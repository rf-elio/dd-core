Shopware.Module.register('elio-fact-finder', {
    type: 'core',
    name: 'FACTFinder',
    title: 'ff.global.title',
    description: 'ff.global.description',
    color: '#014587',
    icon: 'default-action-tags',

    routes: {
        index: {
            component: 'sw-cms-list',
            path: 'index',
            meta: {
                privilege: 'cms.viewer',
            },
        },
        detail: {
            component: 'sw-cms-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sw.cms.index',
                privilege: 'cms.viewer',
            },
        },
        create: {
            component: 'sw-cms-create',
            path: 'create',
            meta: {
                parentPath: 'sw.cms.index',
                privilege: 'cms.creator',
            },
        },
    },

    navigation: [{
        id: 'elio-fact-finder',
        label: 'ff.global.title',
        color: '#014587',
        icon: 'default-shopping-paper-bag-product',
        position: 100
    }]
});