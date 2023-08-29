Shopware.Module.register('elio-search', {
    type: 'core',
    name: 'ElioSearch',
    title: 'elio.search.global.title',
    description: 'elio.search.global.description',
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
        id: 'elio-search',
        label: 'elio.search.global.title',
        color: '#014587',
        icon: 'regular-products',
        position: 100
    }]
});