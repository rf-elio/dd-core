import './component';
import './preview';
import './config';

Shopware.Service('cmsService').registerCmsElement({
    name: 'edd-cms-slider',
    label: 'sw-cms.elements.cmsSlider.label',
    component: 'sw-cms-el-edd-cms-slider',
    configComponent: 'sw-cms-el-config-edd-cms-slider',
    previewComponent: 'sw-cms-el-preview-edd-cms-slider',
    defaultConfig: {
        cmsSliderParameterName: {
            source: 'static',
            value: 'cmsSliderId',
            required: true
        },
        cmsSliderParameterValue: {
            source: 'static',
            value: '',
            required: true
        },
        elMinWidth: {
            source: 'static',
            value: '200px'
        }
    }
})
