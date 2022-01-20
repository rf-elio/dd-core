import './component';
import './preview';
import './config';

Shopware.Service('cmsService').registerCmsElement({
    name: 'ff-advisor-campaign',
    label: 'sw-cms.elements.advisorCampaign.label',
    component: 'sw-cms-el-ff-advisor-campaign',
    configComponent: 'sw-cms-el-config-ff-advisor-campaign',
    previewComponent: 'sw-cms-el-preview-ff-advisor-campaign',
    defaultConfig: {
        campaignName: {
            source: 'static',
            value: ''
        },
        productsTitle: {
            source: 'static',
            value: ''
        }
    }
})
