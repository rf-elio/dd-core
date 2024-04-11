import './component';
import './preview';
import './config';

Shopware.Service('cmsService').registerCmsElement({
    name: 'edd-advisor-campaign',
    label: 'sw-cms.elements.advisorCampaign.label',
    component: 'sw-cms-el-edd-advisor-campaign',
    configComponent: 'sw-cms-el-config-edd-advisor-campaign',
    previewComponent: 'sw-cms-el-preview-edd-advisor-campaign',
    defaultConfig: {
        campaignParameterName: {
            source: 'static',
            value: 'campaignId'
        },
        campaignParameterValue: {
            source: 'static',
            value: ''
        },
        productsTitle: {
            source: 'static',
            value: ''
        }
    }
})
