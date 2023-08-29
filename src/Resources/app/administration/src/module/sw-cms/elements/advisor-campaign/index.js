import './component';
import './preview';
import './config';

Shopware.Service('cmsService').registerCmsElement({
    name: 'elio-search-advisor-campaign',
    label: 'sw-cms.elements.advisorCampaign.label',
    component: 'sw-cms-el-elio-search-advisor-campaign',
    configComponent: 'sw-cms-el-config-elio-search-advisor-campaign',
    previewComponent: 'sw-cms-el-preview-elio-search-advisor-campaign',
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
