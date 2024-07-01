import HttpClient from 'src/service/http-client.service';
import TrackingUtil from './../../utility/tracking.util'

/**
 * This plugin submits the saved trackings
 */
export default class TrackingWorkerPlugin extends window.PluginBaseClass {
    init() {
        const me = this;
        me._client = new HttpClient();

        window.setTimeout(function () {
            me._submitTrackings();
        }, 2000);
    }
    _submitTrackings() {
        const me = this;
        const items = TrackingUtil.getAll();
        TrackingUtil.clear();

        for (const item of items) {
            const path = item.path;
            const payload = item.payload;
            me._client.post(path, JSON.stringify(payload));
        }
    }
}
