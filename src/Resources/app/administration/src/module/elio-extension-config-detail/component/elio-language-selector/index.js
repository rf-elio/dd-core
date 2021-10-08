import template from './elio-language-selector.html.twig';
import './elio-language-selector.scss';

Shopware.Component.register('elio-language-selector', {
    template: template,

    props: {
        languages: {
            type: Array,
            required: false,
            default() {
                return [{'id': 'null', 'value': 'All', 'active': true}];
            }
        }
    },

    methods: {
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
                this.languageId = pickedSpan.dataset.selectorValue;
                pickedSpan.closest('.elio-language-selector__inner').querySelector('button').querySelector('span').innerText = pickedSpan.innerText

                this.$emit('on-selected', this.languageId);
                //this.$root.$emit('on-change-application-language', { languageId: this.languageId });
            }
            this.closeSelector();
        },

        /**
         * close language selector
         */
        closeSelector() {
            var selector = document.querySelector('.elio-language-selector');
            if (!selector) {
                return;
            }
            if (selector.classList.contains('elio-language-selector--opened')) {
                selector.classList.remove('elio-language-selector--opened');
            }
        }
    }
});