import TrackingPlugin from './plugin/tracking/tracking-plugin';
import FactFinderFilterPropertySelectPlugin from "./plugin/tracking/ff-filter-property-select.plugin";
import FactFinderFilterRangePlugin from "./plugin/tracking/ff-filter-range.plugin";
import FactFinderFilterTreeSelectPlugin from "./plugin/tracking/ff-filter-tree-select.plugin";

const PluginManager = window.PluginManager;
PluginManager.register('TrackingPlugin', TrackingPlugin, '.elio-ff-listing-box');
PluginManager.register('FactFinderFilterPropertySelect', FactFinderFilterPropertySelectPlugin, '[data-fact-finder-filter-property-select]');
PluginManager.register('FactFinderFilterRange', FactFinderFilterRangePlugin, '[data-fact-finder-filter-range]');
PluginManager.register('FactFinderFilterTreeSelect', FactFinderFilterTreeSelectPlugin, '[data-fact-finder-filter-tree-select]');
