import $ from 'jquery'

export default class Tracking {
    constructor() {
    }

    doTrack(eventName, channel, sessionId, extraParams) {
        let params = {
            event: eventName,
            channel: channel,
            sid: sessionId
        };
        for (let param in extraParams) {
            if (extraParams[param] != null)
                params[param] = extraParams[param];
        }
        $.ajax({
            type: "GET",
            url: "demo/tracking.php",
            data: params,
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            cache: false,
            async: false,
            headers: {
                'sid': sessionId
            }
        });
    }

    click(channel, sessionId, id, masterId, query, pos, origPos,
                     page, origPageSize, campaign, instoreAds) {
        this.doTrack('click', channel, sessionId, {
            id: id,
            masterId: masterId,
            query: query,
            pos: pos,
            origPos: origPos,
            page: page,
            origPageSize: origPageSize,
            campaign: campaign,
            instoreAds: instoreAds
        });
    }

    recommendationClick(channel, sessionId, id, masterId, mainId) {
        this.doTrack('recommendationClick', channel, sessionId, {
            id: id,
            masterId: masterId,
            mainId: mainId
        });
    }

    cart(channel, sessionId, id, masterId, count, price, query, campaign, instoreAds) {
        this.doTrack('cart', channel, sessionId, {
            id: id,
            masterId: masterId,
            count: count,
            price: price,
            query: query,
            campaign: campaign,
            instoreAds: instoreAds
        });
    }

    directCart(channel, sessionId, id, masterId, query, pos,
                          origPos, page, origPageSize, count, price, campaign, instoreAds) {
        this.click(channel, sessionId, id, masterId, query, pos, origPos, page,
            origPageSize, campaign, instoreAds);
        this.cart(channel, sessionId, id, masterId, count, price, query, campaign, instoreAds);
    }

    checkout(channel, sessionId, id, masterId, count, price, query,
                        userId, campaign, instoreAds) {
        this.doTrack('checkout', channel, sessionId, {
            id: id,
            masterId: masterId,
            count: count,
            price: price,
            query: query,
            userId: userId,
            campaign: campaign,
            instoreAds: instoreAds
        });
    }

    login(channel, sessionId, userId) {
        this.doTrack('login', channel, sessionId, {
            userId: userId
        });
    }
}
