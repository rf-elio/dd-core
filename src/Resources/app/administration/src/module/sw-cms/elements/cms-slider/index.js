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
            value: 'Slider Name',
            required: true
        },
        cmsSliderParameterValue: {
            source: 'static',
            value: '',
            required: true
        },
        displayMode: {
            source: 'static',
            value: 'standard',
        },
        boxLayout: {
            source: 'static',
            value: 'standard',
        },
        navigation: {
            source: 'static',
            value: true,
        },
        rotate: {
            source: 'static',
            value: false,
        },
        border: {
            source: 'static',
            value: false,
        },
        elMinWidth: {
            source: 'static',
            value: '250px'
        }
    }
})
