import TrackingPlugin from './plugin/tracking/tracking-plugin';
import FactFinderFilterPropertySelectPlugin from "./plugin/tracking/ff-filter-property-select.plugin";
import FactFinderFilterRangePlugin from "./plugin/tracking/ff-filter-range.plugin";
import FactFinderFilterTreeSelectPlugin from "./plugin/tracking/ff-filter-tree-select.plugin";
import ElioSuggestAutocompletePlugin from "./plugin/elio-suggest-autocomplete/elio-suggest-autocomplete.plugin";
import ElioSearchWidgetPlugin from "./plugin/elio-search-widget/elio-search-widget.plugin";
import ElioProductDetailCrossSellingPlugin from "./plugin/elio-product-detail-cross-selling/elio-product-detail-cross-selling.plugin";
import ElioSearchHistoryPlugin from "./plugin/elio-search-history/elio-search-history.plugin";
import ElioSearchTrackerPlugin from "./plugin/elio-search-tracker/elio-search-tracker.plugin";
import AdvisorCampaignPlugin from "./plugin/advisor-campaign/advisor-campaign.plugin";

const PluginManager = window.PluginManager;
PluginManager.register('TrackingPlugin', TrackingPlugin, '.elio-ff-listing-box');
PluginManager.register('FactFinderFilterPropertySelect', FactFinderFilterPropertySelectPlugin, '[data-fact-finder-filter-property-select]');
PluginManager.register('FactFinderFilterRange', FactFinderFilterRangePlugin, '[data-fact-finder-filter-range]');
PluginManager.register('FactFinderFilterTreeSelect', FactFinderFilterTreeSelectPlugin, '[data-fact-finder-filter-tree-select]');
PluginManager.register('ElioSuggestAutocompletePlugin', ElioSuggestAutocompletePlugin, '.e-header-search-form');
PluginManager.register('ElioSearchTrackerPlugin', ElioSearchTrackerPlugin, '[data-search-tracker]');
PluginManager.register('ElioSearchHistory', ElioSearchHistoryPlugin, '.e-search-history');
PluginManager.override('SearchWidget', ElioSearchWidgetPlugin, '[data-search-form]');
PluginManager.register('ElioProductDetailCrossSellingPlugin', ElioProductDetailCrossSellingPlugin, '[data-e-ff-product-detail-cross-selling-url]');
PluginManager.register('FactFinderAdvisorCampaign', AdvisorCampaignPlugin, '[data-fact-finder-advisor-campaign]')
