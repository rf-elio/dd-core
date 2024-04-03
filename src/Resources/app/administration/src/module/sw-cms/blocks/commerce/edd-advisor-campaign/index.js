import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'edd-advisor-campaign',
    category: 'commerce',
    label: 'sw-cms.blocks.commerce.advisorCampaign.label',
    component: 'sw-cms-block-edd-advisor-campaign',
    previewComponent: 'sw-cms-preview-edd-advisor-campaign',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        advisorCampaign: 'edd-advisor-campaign'
    }
});
