export default class TrackingUtil {
    static storageKey = 'elio-search-tracking';

    static add(path, payload) {
        const items = this.getAll();
        items.push({path: path, payload: payload});
        localStorage.setItem(TrackingUtil.storageKey, JSON.stringify(items));
    }

    static getAll() {
        return JSON.parse(localStorage.getItem(TrackingUtil.storageKey) || null) || [];
    }

    static clear() {
        localStorage.removeItem(TrackingUtil.storageKey);
    }
}
