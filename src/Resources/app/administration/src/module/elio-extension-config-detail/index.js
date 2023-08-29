import template from './elio-plugin-config-detail.html.twig';
import './elio-plugin-config-detail.scss';

(async function initDependencies() {
    await import(/* webpackMode: 'eager' */ './component/elio-language-selector');
})();

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
            isLanguageDivided: false,
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
            if (this.namespace === 'ElioSearch') {
                this.isLanguageDivided = true;
                await this.loadLanguages();
                this.updateLanguage();
            }
        },

        /**
         *
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
         *
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
         *
         * loading languages to language selector
         */
        loadLanguages() {
            var operator = this;
            this.languageRepository.search(new Criteria(), Shopware.Context.api).then(languages => {
                if (languages.length > 0) {
                    languages.forEach((language) => {
                        operator.languages.push({'id': language.id, 'value': language.name, 'active': false});
                    });
                }
            });
        },

        /**
         *
         * on changing language
         */
        onChangeLanguage(languageId) {
            //Shopware.State.commit('context/setApiLanguageId', languageId);
            this.languageId = languageId;
        },

        /**
         *
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
    }
});

// TODO: in future we may could rewrite this to own admin module, to extend the shopware standard functionality
// where the plugin settings could be language different.
Shopware.Application.getContainer().factory.template.elioPostRegisterComponentTemplate = function (componentName) {
    if (componentName == 'sw-extension-config') {
        Shopware.Application.getContainer().factory.template.getTemplateRegistry().set(componentName, {
            name: componentName,
            raw: template,
            extend: null,
            overrides: [],
        });
    }
};

const registerComponentTemplate = Shopware.Application.getContainer().factory.template.registerComponentTemplate;
Shopware.Application.getContainer().factory.template.registerComponentTemplate = function (
    componentName,
    componentTemplate = null
) {
    registerComponentTemplate(
        componentName,
        componentTemplate,
    );
    if (Shopware.Application.getContainer().factory.template.elioPostRegisterComponentTemplate) {
        Shopware.Application.getContainer().factory.template.elioPostRegisterComponentTemplate(componentName, componentTemplate);
    }
}