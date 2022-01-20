import Plugin from 'src/plugin-system/plugin.class';
import PluginManager from 'src/plugin-system/plugin.manager';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import Debouncer from 'src/helper/debouncer.helper';
import storage from 'src/helper/storage/storage.helper';

const STORAGE_ITEM_NAME = 'elio-ff-advisor-is-completed';
const ANSWER_DATA_ATTR = 'data-elio-ff-advisor-answer';
const QUESTION_DATA_ATTR = 'data-elio-ff-advisor-question';
const SCROLL_DEBOUNCE = 50;

export default class AdvisorCampaignPlugin extends Plugin {
    static options = {
        productsContainerSelector: '[data-elio-ff-advisor-products]',
        productsTitleSelector: '[data-elio-ff-advisor-products-title]',
        questionsContainerSelector: '[data-elio-ff-advisor-questions]',
        sliderSelector: '[data-product-slider]',
        pluginSliderName: 'ProductSlider',
        productsUrl: null,
        campaignUrl: null,
        campaignName: null,
        campaignId: null,
        productsToken: null,
        campaignToken: null,
        questions: [],
        showPassedAfterDays: 0
    }

    init () {
        if (this._isAdvisorCompleted()) {
            return;
        }

        this._client = new HttpClient();
        this._answerPath = [];
        this._questionsContainer = this.el.querySelector(this.options.questionsContainerSelector);
        this._productsContainer = this.el.querySelector(this.options.productsContainerSelector);
        this._productsTitle = this.el.querySelector(this.options.productsTitleSelector);

        this._registerEvents();
        if (this.options.questions.length > 0) {
            this._showQuestions(this.options.questions);
        } else if (!this.options.campaignId) {
            this._lazyLoadCampaign();
        }
    }

    _registerEvents () {
        this.el.addEventListener('click', event => {
            const answer = event.target.closest(`[${ANSWER_DATA_ATTR}]`);
            if (answer) {
                this._onAnswer(answer);
            }
        })
    }

    _onAnswer (answer) {
        ElementLoadingIndicatorUtil.create(this.el);
        this._answerPath.push([
            answer.closest(`[${QUESTION_DATA_ATTR}]`).getAttribute('data-index'),
            answer.getAttribute('data-index')
        ]);

        this._load(this.options.productsUrl, {
            _csrf_token: this.options.productsToken,
            advisorStatus: this._getAdvisorStatus()
        }).then(response => {
            this._questionsContainer.innerHTML = '';

            if (response.productsCount > 0) {
                this._productsContainer.innerHTML = response.data;
                if (this._productsTitle) this._productsTitle.classList.add('active');
                PluginManager.initializePlugin(this.options.pluginSliderName, this.options.sliderSelector);
                this._setAdvisorCompleted();
            } else {
                const questions = this._getQuestionsByAnswerPath();
                this._showQuestions(questions);
            }
        });
    }

    _lazyLoadCampaign () {
        document.addEventListener(
            'scroll',
            Debouncer.debounce(this._onScrollEnd.bind(this), SCROLL_DEBOUNCE),
            true
        );
        document.dispatchEvent(new Event('scroll'));
    }

    _onScrollEnd () {
        if (!this._campaignIsLoaded && this._isElInViewport()) {
            this._campaignIsLoaded = true;
            this._loadCampaign();
        }
    }

    _loadCampaign () {
        this._load(this.options.campaignUrl, {
            _csrf_token: this.options.campaignToken,
            campaignName: this.options.campaignName
        }).then(response => {
            if (response.data) {
                this.options.campaignId = response.data.id;
                this.options.questions = response.data.questions;

                if (!this._isAdvisorCompleted()) {
                    this._showQuestions(this.options.questions);
                }
            }
        });
    }

    _load (url, params) {
        ElementLoadingIndicatorUtil.create(this.el);

        return new Promise((resolve, reject) => {
            this._client.post(url, JSON.stringify(params), response => {
                response = JSON.parse(response);
                if (response.success) {
                    resolve(response);
                } else {
                    console.log('error', response);
                    reject(response);
                }
                ElementLoadingIndicatorUtil.remove(this.el);
            });
        });
    }

    _showQuestions (questions) {
        let html = '';
        questions.forEach((question, qIdx) => {
            html += `<div class="elio-ff-advisor__question-container" ${QUESTION_DATA_ATTR} data-index="${qIdx}">` +
                `<div class="elio-ff-advisor__question">${question['text']}</div>` +
                '<div class="elio-ff-advisor__answers row justify-content-start align-items-stretch">';

            question['answers'].forEach((answer, idx) => {
                html += '<div class="elio-ff-advisor__answer-wrapper col-6 col-md-3 col-lg-3">' +
                    `<div class="elio-ff-advisor__answer" ${ANSWER_DATA_ATTR} data-index="${idx}">` +
                    `${answer['text']}` +
                    `</div></div>`;
            });

            html += '</div></div>';
        });

        this._questionsContainer.innerHTML = html;
    }

    _getQuestionsByAnswerPath () {
        let questions = this.options.questions;
        this._answerPath.forEach(path => {
            questions = questions[path[0]]['answers'][path[1]]['questions'];
        })
        return questions;
    }

    _getAdvisorStatus () {
        return {
            answerPath: '_' + this._answerPath.map(path => `${path[0]}_${path[1]}`).join('_'),
            id: this.options.campaignId
        }
    }

    _setAdvisorCompleted () {
        const expire = Date.now() + ((60 * 60 * 24 * this.options.showPassedAfterDays) * 1000);
        storage.setItem(STORAGE_ITEM_NAME + '_' + this.options.campaignId, expire);
    }

    _isAdvisorCompleted () {
        if (!this.options.campaignId) {
            return false;
        }
        const expire = storage.getItem(STORAGE_ITEM_NAME + '_' + this.options.campaignId);
        return expire && parseInt(expire) > Date.now();
    }

    _isElInViewport () {
        const rect = this.el.getBoundingClientRect();
        return rect.top > 0 &&
            rect.left > 0 &&
            rect.bottom > 0 &&
            rect.right > 0 &&
            rect.bottom <= window.innerHeight &&
            rect.right <= window.innerWidth;
    }
}
