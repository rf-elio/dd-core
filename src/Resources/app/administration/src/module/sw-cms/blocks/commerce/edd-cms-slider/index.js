import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'edd-cms-slider',
    category: 'commerce',
    label: 'sw-cms.blocks.commerce.cmsSlider.label',
    component: 'sw-cms-block-edd-cms-slider',
    previewComponent: 'sw-cms-preview-edd-cms-slider',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        cmsSlider: 'edd-cms-slider'
    }
});
