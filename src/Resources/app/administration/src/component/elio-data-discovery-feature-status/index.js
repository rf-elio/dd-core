import template from './feature-status.html.twig';
const {Component} = Shopware;

Component.register('elio-data-discovery-feature-status', {
    template,

    props: {
        feature: {
            type: String,
            required: true
        }
    },

    created() {
        this.onCreated();
    },

    data() {
        return {
            isLoading: false,
            isEnabled: false,
        }
    },

    computed: {
        notificationVariant() {
            return (this.isEnabled) ? 'success' : 'warning';
        },
        statusText() {
            return this.$tc('elio-data-discovery.features.' + ((this.isEnabled) ? 'enabled' : 'disabled'), 0, {feature: this.feature});
        }
    },

    methods: {
        onCreated() {
            const me = this;
            this.isLoading = true;
            const httpClient = Shopware.Service('syncService').httpClient;
            const url = '/_action/elio-data-discovery/features/' + this.feature;
            const basicHeaders = {
                Authorization: `Bearer ${Shopware.Context.api.authToken.access}`,
                'Content-Type': 'application/json'
            };

            httpClient
                .get(url, {
                    headers: basicHeaders
                })
                .then((response) => {
                    me.handle(response.data);
                })
                .catch((error) => {
                    me.handle(null);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        handle(data) {
            if (data.hasOwnProperty('enabled')) {
                this.isEnabled = data.enabled;
            }
        }
    }
});
