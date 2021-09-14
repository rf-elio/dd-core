import template from './elio-fact-finder-restriction-ruler.html.twig';
import '../../page/ff-restrictions-index/ff-restrictions-index.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-restriction-ruler', {
    template: template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        isCategory: {
            type: Boolean,
            required: true,
            default() {
                return false;
            }
        },
        layer: {
            type: String,
            required: false,
            default() {
                return 'global';
            }
        },
        category_id: {
            type: String,
            required: false,
        },
    },

    computed: {
        isLoading() {
            return false;
        },
        allowAllChecked() {
            return false;
        },
        blockAllChecked() {
            return false;
        }
    },

    data() {
        return {
            currentDragItem: null
        }
    },

    created() {
        this.onCreated();
    },

    methods: {
        onCreated() {

        },

        onDragStart(dragData) {
            if (dragData.target.className === "filter") {
                this.currentDragItem = dragData.target;
            }
        },

        onDrop(dragData) {
            if (dragData.target.classList.contains("ruler-tab-filter-list")) {
                dragData.preventDefault();
                this.currentDragItem.parentNode.removeChild(this.currentDragItem);
                dragData.target.appendChild(this.currentDragItem);
            }
        },

        onAllowAllClicked() {
            console.log('onAllowAllClicked');
        },

        onAllowSelectedClicked() {
            console.log('onAllowSelectedClicked');
        },

        onBlockAllClicked() {
            console.log('onBlockAllClicked');
        },

        onBlockSelectedClicked() {
            console.log('onBlockSelectedClicked');
        }
    }
});