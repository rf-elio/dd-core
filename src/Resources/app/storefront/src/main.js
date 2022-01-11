import TrackingPlugin from './plugin/tracking/tracking-plugin';
import FactFinderFilterPropertySelectPlugin from "./plugin/tracking/ff-filter-property-select.plugin";
import FactFinderFilterRangePlugin from "./plugin/tracking/ff-filter-range.plugin";
import FactFinderFilterTreeSelectPlugin from "./plugin/tracking/ff-filter-tree-select.plugin";
import ElioSuggestAutocompletePlugin from "./plugin/elio-suggest-autocomplete/elio-suggest-autocomplete.plugin";
import ElioSearchWidgetPlugin from "./plugin/header/elio-search-widget.plugin";
import ElioSearchHistoryPlugin from "./plugin/elio-search-history/elio-search-history.plugin";
import ElioSearchTrackerPlugin from "./plugin/elio-search-tracker/elio-search-tracker.plugin";

const PluginManager = window.PluginManager;
PluginManager.register('TrackingPlugin', TrackingPlugin, '.elio-ff-listing-box');
PluginManager.register('FactFinderFilterPropertySelect', FactFinderFilterPropertySelectPlugin, '[data-fact-finder-filter-property-select]');
PluginManager.register('FactFinderFilterRange', FactFinderFilterRangePlugin, '[data-fact-finder-filter-range]');
PluginManager.register('FactFinderFilterTreeSelect', FactFinderFilterTreeSelectPlugin, '[data-fact-finder-filter-tree-select]');
PluginManager.register('ElioSuggestAutocompletePlugin', ElioSuggestAutocompletePlugin, '.e-header-search-form');
PluginManager.register('ElioSearchTrackerPlugin', ElioSearchTrackerPlugin, '[data-search-tracker]');
PluginManager.register('ElioSearchHistoryPlugin', ElioSearchHistoryPlugin, '.e-search-history .header-search-input');
PluginManager.override('SearchWidget', ElioSearchWidgetPlugin, '[data-search-form]');