import TrackingPlugin from './plugin/tracking/tracking-plugin';

const PluginManager = window.PluginManager;
PluginManager.register('TrackingPlugin', TrackingPlugin, '.elio-ff-listing-box');
