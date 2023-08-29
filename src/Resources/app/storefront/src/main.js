import TrackingPlugin from './plugin/tracking/tracking-plugin';
import TrackingWorkerPlugin from './plugin/tracking/tracking-worker-plugin';
import ElioSearchFilterPropertySelectPlugin from "./plugin/filter/elio-search-filter-property-select.plugin";
import ElioSearchFilterRangePlugin from "./plugin/filter/elio-search-filter-range.plugin";
import ElioSearchFilterTreeSelectPlugin from "./plugin/filter/elio-search-filter-tree-select.plugin";
import ElioSuggestAutocompletePlugin from "./plugin/elio-suggest-autocomplete/elio-suggest-autocomplete.plugin";
import ElioSearchWidgetPlugin from "./plugin/elio-search-widget/elio-search-widget.plugin";
import ElioProductDetailCrossSellingPlugin from "./plugin/elio-product-detail-cross-selling/elio-product-detail-cross-selling.plugin";
import ElioSearchHistoryPlugin from "./plugin/elio-search-history/elio-search-history.plugin";
import ElioSearchTrackerPlugin from "./plugin/elio-search-tracker/elio-search-tracker.plugin";
import ElioAdvisorCampaignPlugin from "./plugin/elio-advisor-campaign/elio-advisor-campaign.plugin";
import ElioAdvisorCampaignCmsLoaderPlugin from "./plugin/elio-advisor-campaign/elio-advisor-campaign-cms-loader.plugin";
import ElioListingPluginExtension from "./plugin/listing/listing.plugin";

const PluginManager = window.PluginManager;
PluginManager.register('TrackingPlugin', TrackingPlugin, '.elio-search-listing-box');
PluginManager.register('TrackingWorkerPlugin', TrackingWorkerPlugin, document);
PluginManager.register('ElioSearchFilterPropertySelect', ElioSearchFilterPropertySelectPlugin, '[data-elio-search-filter-property-select]');
PluginManager.register('ElioSearchFilterRange', ElioSearchFilterRangePlugin, '[data-elio-search-filter-range]');
PluginManager.register('ElioSearchFilterTreeSelect', ElioSearchFilterTreeSelectPlugin, '[data-elio-search-filter-tree-select]');
PluginManager.register('ElioSuggestAutocompletePlugin', ElioSuggestAutocompletePlugin, '.e-header-search-form');
PluginManager.register('ElioSearchTrackerPlugin', ElioSearchTrackerPlugin, '[data-search-tracker]');
PluginManager.register('ElioSearchHistory', ElioSearchHistoryPlugin, '.e-search-history');
PluginManager.override('SearchWidget', ElioSearchWidgetPlugin, '[data-search-form]');
PluginManager.register('ElioProductDetailCrossSellingPlugin', ElioProductDetailCrossSellingPlugin, '[data-e-elio-search-product-detail-cross-selling-url]');
PluginManager.register('ElioAdvisorCampaignPlugin', ElioAdvisorCampaignPlugin, '.e-elio-search-advisor-campaign')
PluginManager.register('ElioAdvisorCampaignCmsLoaderPlugin', ElioAdvisorCampaignCmsLoaderPlugin, '.e-elio-search-advisor-campaign-lazy')
PluginManager.register('ElioListingPluginExtension', ElioListingPluginExtension, '[data-listing]')
