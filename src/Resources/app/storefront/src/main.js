import TrackingPlugin from './plugin/tracking/tracking-plugin';
import TrackingWorkerPlugin from './plugin/tracking/tracking-worker-plugin';
import ElioDataDiscoveryFilterPropertySelectPlugin from "./plugin/filter/elio-data-discovery-filter-property-select.plugin";
import ElioDataDiscoveryFilterRangePlugin from "./plugin/filter/elio-data-discovery-filter-range.plugin";
import ElioDataDiscoveryFilterTreeSelectPlugin from "./plugin/filter/elio-data-discovery-filter-tree-select.plugin";
import ElioSuggestAutocompletePlugin from "./plugin/elio-suggest-autocomplete/elio-suggest-autocomplete.plugin";
import ElioDataDiscoveryWidgetPlugin from "./plugin/elio-data-discovery-widget/elio-data-discovery-widget.plugin";
import ElioProductDetailCrossSellingPlugin from "./plugin/elio-product-detail-cross-selling/elio-product-detail-cross-selling.plugin";
import ElioDataDiscoveryHistoryPlugin from "./plugin/elio-data-discovery-history/elio-data-discovery-history.plugin";
import ElioDataDiscoveryTrackerPlugin from "./plugin/elio-data-discovery-tracker/elio-data-discovery-tracker.plugin";
import ElioListingPluginExtension from "./plugin/listing/listing.plugin";

const PluginManager = window.PluginManager;
PluginManager.register('TrackingPlugin', TrackingPlugin, '.elio-data-discovery-listing-box');
PluginManager.register('TrackingWorkerPlugin', TrackingWorkerPlugin, document);
PluginManager.register('ElioDataDiscoveryFilterPropertySelect', ElioDataDiscoveryFilterPropertySelectPlugin, '[data-elio-data-discovery-filter-property-select]');
PluginManager.register('ElioDataDiscoveryFilterRange', ElioDataDiscoveryFilterRangePlugin, '[data-elio-data-discovery-filter-range]');
PluginManager.register('ElioDataDiscoveryFilterTreeSelect', ElioDataDiscoveryFilterTreeSelectPlugin, '[data-elio-data-discovery-filter-tree-select]');
PluginManager.register('ElioSuggestAutocompletePlugin', ElioSuggestAutocompletePlugin, '.e-header-search-form');
PluginManager.register('ElioDataDiscoveryTrackerPlugin', ElioDataDiscoveryTrackerPlugin, '[data-search-tracker]');
PluginManager.register('ElioDataDiscoveryHistory', ElioDataDiscoveryHistoryPlugin, '.e-search-history');
PluginManager.override('SearchWidget', ElioDataDiscoveryWidgetPlugin, '[data-search-form]');
PluginManager.register('ElioProductDetailCrossSellingPlugin', ElioProductDetailCrossSellingPlugin, '[data-e-elio-data-discovery-product-detail-cross-selling-url]');
PluginManager.register('ElioListingPluginExtension', ElioListingPluginExtension, '[data-listing]')
