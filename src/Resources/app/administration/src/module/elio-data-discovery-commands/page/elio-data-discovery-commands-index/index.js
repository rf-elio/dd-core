import template from './elio-data-discovery-commands-index.html.twig';
import './elio-data-discovery-commands-index.scss';

const {Mixin} = Shopware;

Shopware.Component.register('elio-data-discovery-commands-index', {
    template,

    inject: [
        'elioDataDiscoverySyncProfile'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            isLoading: false,
            commands: {
                "indexUpdate": {
                    'handler': 'simpleCommandGet',
                    'endpoint': 'index-update',
                    'featureFlag': 'syncProfile'
                },
                "productSortUpdate": {
                    'handler': 'simpleCommandGet',
                    'endpoint': 'recalculate-sort',
                    'featureFlag': ''
                },
                "rankingUpdate": {
                    'handler': 'simpleCommandGet',
                    'endpoint': 'ranking-update',
                    'featureFlag': ''
                },
                "categoryInheritanceUpdate": {
                    'handler': 'simpleCommandGet',
                    'endpoint': 'category-inheritance-update',
                    'featureFlag': ''
                },
                "syncData": {
                    'handler': 'generateSyncData',
                    'endpoint': '',
                    'argument': {
                        'name': 'syncProfileId',
                        'value': '',
                        'placeholder': '<syncProfileId>'
                    },
                    'updateInterval': 3000,
                    'status': null,
                    'featureFlag': 'syncProfile'
                }
            }
        }
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    methods: {
        header(command) {
            return this.$tc('elio-data-discovery-commands.' + command + '.header')
        },
        description(command) {
            return this.$tc('elio-data-discovery-commands.' + command + '.description')
        },
        button(command) {
            return this.$tc('elio-data-discovery-commands.' + command + '.button')
        },
        getCommandText(command) {
            return this.$tc('elio-data-discovery-commands.' + command + '.command')
        },
        getCommandArgumentInput(command) {
            if (this.commands[command].argument) {
                return this.commands[command].argument;
            }
            return null;
        },
        handle(handler, command) {
            if (typeof this[handler] === 'function') {
                this[handler](command);
            }
        },

        simpleCommandGet(command) {
            this.isLoading = true;
            this.commands[command].processing = true;

            const initContainer = Shopware.Application.getContainer('init');
            const headers = {
                Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
            };
            const endpoint = this.commands[command].endpoint;

            initContainer.httpClient.get(('/_action/elio-data-discovery/' + endpoint), {headers}).then((response) => {
                let feedback = '';
                if (!response.data || response.data === '') {
                    feedback = this.$tc('elio-data-discovery-commands.commandFinishedSuccess')
                } else if (response.data.mode === 'async') {
                    feedback = this.$tc('elio-data-discovery-commands.commandStartedAsync')
                }
                this.createNotificationSuccess({
                    message: feedback ?? ''
                });
                this.isLoading = false;
                this.commands[command].processing = false;
            }).catch((error) => {
                this.createNotificationError({
                    message: error.response.data ?? 'error'
                });
                this.isLoading = false;
                this.commands[command].processing = false;
            });
        },

        generateSyncData(command) {
            this.isLoading = true;
            // this.commands[command].processing = true; JS/VUE bug

            const that = this;
            this.commands[command].updateTimer = setTimeout(function requestStatus() {
                that._updateStatus(command);
                if (that.commands[command].status === true) {
                    that.createNotificationSuccess({
                        title: that.$tc('global.default.success'),
                        message: that.$tc('elio-data-discovery-sync-profile.detail.messageGeneratingSuccess')
                    });
                    clearTimeout(that.commands[command].updateTimer);
                } else {
                    that.commands[command].updateTimer = setTimeout(requestStatus, that.commands[command].updateInterval || 3000);
                }
            }, that.commands[command].updateInterval || 3000);

            this.elioDataDiscoverySyncProfile.generate(this.commands[command].argument.value).then((responce) => {
                that.isLoading = false;
                this.createNotificationSuccess({
                    message: this.$tc('elio-data-discovery-commands.commandStartedAsync')
                });
            }).catch((exception) => {
                this.createNotificationError({
                    message: this.$tc('elio-data-discovery-sync-profile.detail.messageGeneratingError', 0, {
                        error: exception.message,
                    })
                });
                this.isLoading = false;
                this.commands[command].processing = false;
            });
        },

        _updateStatus(command) {
            this.elioDataDiscoverySyncProfile.getStatus(this.commands[command].argument.value).then((response) => {
                if (response.data.finished === true) {
                    this.commands[command].status = true;
                }
            })
        },
    }
})
