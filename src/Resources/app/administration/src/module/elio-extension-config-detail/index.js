import template from './elio-plugin-config-detail.html.twig';
import './elio-plugin-config-detail.scss';

const {Criteria} = Shopware.Data;

Shopware.Component.override('sw-extension-config', {
    template: template,

    inject: [
        'repositoryFactory'
    ],

    created() {
        this.onCreated();
    },

    data() {
        return {
            salesChannelId: null,
            languageId: null,
            languageNameSpace: '',
            languages: [{'id': 'null', 'value': 'All', 'active': true}]
        };
    },

    computed: {
        languageRepository() {
            return this.repositoryFactory.create('language');
        },
        defaultLanguageCriteria() {
            var criteria = new Criteria();
            criteria.addAssociation('locale');
            criteria.addFilter(Criteria.equals('language.id', this.languageId));
            return criteria;
        }
    },

    watch: {
        languageId() {
            this.updateLanguage();
        },
        languageNameSpace() {
            this.updateActualConfigData();
        }
    },

    methods: {
        async onCreated() {
            await this.loadLanguages();
            this.updateLanguage();
        },

        /**
         * remake this.$refs.systemConfig.config for new names with new languageNameSpace
         */
        updateActualConfigData() {
            var operator = this;

            if (this.$refs.systemConfig.config) {
                this.$refs.systemConfig.config.forEach((keyValue) => {
                    keyValue.elements.forEach((configElem) => {
                        var splited = configElem['name'].split('.');
                        var newName = '';
                        splited.forEach((entry, i) => {
                            newName += (i > 0 ? '.' : '') + (i === (splited.length - 1) ?
                                ((operator.languageNameSpace !== '') ? operator.languageNameSpace + '_' : '') + entry.split('_')[entry.split('_').length - 1]
                                : entry);
                        });
                        configElem['name'] = newName;
                    });
                });
            }
        },

        /**
         * on saving configuration
         */
        onSave() {
            this.$refs.systemConfig.saveAll().then(() => {
                this.createNotificationSuccess({
                    message: this.$tc('sw-extension-store.component.sw-extension-config.messageSaveSuccess')
                });
            }).catch((err) => {
                this.createNotificationError({
                    message: err
                });
            });
        },

        /**
         * loading languages to language selector
         */
        loadLanguages() {
            var operator = this;
            this.languageRepository.search(new Criteria(), Shopware.Context.api).then(languages => {
                if (languages.length > 0) {
                    languages.forEach((language) => {
                        operator.languages.push({'id': language.id, 'value': language.name});
                    });
                }
            });
        },

        /**
         * on changing language
         */
        onChangeLanguage(languageId) {
            //Shopware.State.commit('context/setApiLanguageId', languageId);
            this.languageId = languageId;
        },

        /**
         * on changing language we change languageNameSpace
         */
        async updateLanguage() {
            if (this.languageId === 'null') {
                this.languageNameSpace = '';
            } else {
                var operator = this;
                await this.languageRepository.search(this.defaultLanguageCriteria, Shopware.Context.api).then(languages => {
                    if (languages.length > 0) {
                        operator.languageNameSpace = languages[0].locale.code;
                    }
                });
            }
        },

        /**
         * onClick to open language selector
         */
        openSelector(event) {
            var selector = event.target.closest('.elio-language-selector');
            if (!selector) {
                return;
            }

            if (!selector.classList.contains('elio-language-selector--opened')) {
                selector.classList.add('elio-language-selector--opened');
            } else {
                selector.classList.remove('elio-language-selector--opened');
            }
        },

        /**
         * onClick on chosen language in selector
         */
        pickSelector(event) {
            var selector = event.target.closest('.elio-language-selector');
            if (!selector) {
                return;
            }

            var pickedSpan = event.target.closest('.elio-language-selector__list-item').querySelector('span');
            if (pickedSpan) {
                this.onChangeLanguage(pickedSpan.dataset.selectorValue);
                pickedSpan.closest('.elio-language-selector__inner').querySelector('button').querySelector('span').innerText = pickedSpan.innerText
            }

            if (selector.classList.contains('elio-language-selector--opened')) {
                selector.classList.remove('elio-language-selector--opened');
            }
        }
    }
});

Shopware.Template.register('sw-extension-config', template);