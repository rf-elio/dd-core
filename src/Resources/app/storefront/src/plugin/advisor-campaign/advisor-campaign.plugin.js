import Plugin from 'src/plugin-system/plugin.class';
import PluginManager from 'src/plugin-system/plugin.manager';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import storage from 'src/helper/storage/storage.helper';

const STORAGE_ITEM_NAME = 'elio-ff-advisor-passed'

export default class AdvisorCampaignPlugin extends Plugin {
    static options = {
        answerSelector: '[data-elio-ff-advisor-answer]',
        questionSelector: '[data-elio-ff-advisor-question]',
        productsContainerSelector: '[data-elio-ff-advisor-products]',
        questionsContainerSelector: '[data-elio-ff-advisor-questions]',
        productsUrl: '',
        campaignUrl: '',
        campaignId: null,
        productsToken: '',
        campaignToken: '',
        sliderSelector: '[data-product-slider]',
        pluginSliderName: 'ProductSlider',
        questions: [],
        filters: {}
    }

    init () {
        this._client = new HttpClient();
        this._answerPath = [];
        this._questionsContainer = this.el.querySelector(this.options.questionsContainerSelector);
        this._productsContainer = this.el.querySelector(this.options.productsContainerSelector);

        this._registerEvents();

        if (this._userPassedAdvisor()) {
            return;
        }
        if (this.options.questions.length > 0) {
            this._showQuestions(this.options.questions);
        } else if (!this.options.campaignId) {
            this._loadCampaign();
        }
    }

    _registerEvents () {
        this.el.addEventListener('click', event => {
            const answer = event.target.closest(this.options.answerSelector);
            if (answer) {
                this._onAnswer(answer);
            }
        })
    }

    _onAnswer (answer) {
        ElementLoadingIndicatorUtil.create(this.el);
        this._answerPath.push([
            answer.closest(this.options.questionSelector).getAttribute('data-index'),
            answer.getAttribute('data-index')
        ]);

        this._loadProducts().then(response => {
            this._questionsContainer.innerHTML = '';

            if (response.success && response.productsCount > 0) {
                this._productsContainer.innerHTML = response.data;
                PluginManager.initializePlugin(this.options.pluginSliderName, this.options.sliderSelector);
            } else {
                const questions = this._getQuestionsByAnswerPath();
                this._showQuestions(questions)
            }
            ElementLoadingIndicatorUtil.remove(this.el);
        })
    }

    _loadProducts() {
        return new Promise(resolve => {
            const parameters = {
                _csrf_token: this.options.productsToken,
                advisorStatus: this._getAdvisorStatus(),
                ...this.options.filters
            }
            this._client.post(this.options.productsUrl, JSON.stringify(parameters), response => {
                resolve(JSON.parse(response));
            })
        });
    }

    _loadCampaign () {
        ElementLoadingIndicatorUtil.create(this.el);
        const parameters = {
            _csrf_token: this.options.campaignToken,
            ...this.options.filters
        }
        this._client.post(this.options.campaignUrl, JSON.stringify(parameters), response => {
            response = JSON.parse(response);
            if (response.success) {
                this.options.campaignId = response.data.id;
                this.options.questions = response.data.questions;

                this._showQuestions(this.options.questions);
            }
            ElementLoadingIndicatorUtil.remove(this.el);
        })
    }

    _showQuestions (questions) {
        let html = '';
        questions.forEach((question, qIdx) => {
            html += `<div class="elio-ff-advisor__question-container" data-elio-ff-advisor-question data-index="${qIdx}">` +
                `<div class="elio-ff-advisor__question">${question['text']}</div>` +
                '<div class="elio-ff-advisor__answers row justify-content-start">';

            question['answers'].forEach((answer, idx) => {
                html += '<div class="elio-ff-advisor__answer-wrapper col-6 col-md-3 col-lg-3">' +
                    `<div class="elio-ff-advisor__answer" data-elio-ff-advisor-answer data-index="${idx}">` +
                    `${answer['text']}` +
                    `</div></div>`;
            });

            html += '</div></div>'
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

    _userPassedAdvisor () {
        return this.options.campaignId && storage.getItem(STORAGE_ITEM_NAME + '_' + this.options.campaignId)
    }
}
