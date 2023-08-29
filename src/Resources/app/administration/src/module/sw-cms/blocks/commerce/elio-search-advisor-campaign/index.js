import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'elio-search-advisor-campaign',
    category: 'commerce',
    label: 'sw-cms.blocks.commerce.advisorCampaign.label',
    component: 'sw-cms-block-elio-search-advisor-campaign',
    previewComponent: 'sw-cms-preview-elio-search-advisor-campaign',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        advisorCampaign: 'elio-search-advisor-campaign'
    }
});
