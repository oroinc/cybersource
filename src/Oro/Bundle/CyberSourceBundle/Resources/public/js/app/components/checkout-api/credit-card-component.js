/* global Flex */
define(function(require) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const scriptjs = require('scriptjs');
    const BaseComponent = require('oroui/js/app/components/base/component');
    require('jquery.validate');

    const CreditCardComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            paymentMethod: null,
            captureContext: null,
            flexJsUrl: '//flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js',
            dynamicSelectors: {
                securityCode: '',
                number: ''
            },
            selectors: {
                month: '[data-expiration-date-month]',
                year: '[data-expiration-date-year]',
                form: '[data-credit-card-form]',
                validation: '[data-validation]'
            }
        },

        /**
         * @property {Object}
         */
        microform: null,

        /**
         * @property {Object}
         */
        microformErrors: {
            number: 'empty',
            securityCode: 'empty'
        },

        /**
         * @property {Boolean}
         */
        paymentValidationRequiredComponentState: true,

        /**
         * @property {jQuery}
         */
        $el: null,

        /**
         * @property string
         */
        month: null,

        /**
         * @property string
         */
        year: null,

        /**
         * @property {jQuery}
         */
        $form: null,

        /**
         * @property {Boolean}
         */
        disposable: true,

        listen: {
            'checkout:payment:method:changed mediator': '_onPaymentMethodChanged',
            'checkout:payment:before-transit mediator': '_beforeTransit',
            'checkout:payment:before-hide-filled-form mediator': '_beforeHideFilledForm',
            'checkout:payment:before-restore-filled-form mediator': '_beforeRestoreFilledForm',
            'checkout:payment:remove-filled-form mediator': '_removeFilledForm',
            'checkout-content:initialized mediator': '_refreshPaymentMethod',
            'checkout:place-order:response mediator': '_placeOrderResponse'
        },

        /**
         * @inheritDoc
         */
        constructor: function CreditCardComponent(options) {
            CreditCardComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            $.validator.loadMethod('oropayment/js/validator/credit-card-number');
            $.validator.loadMethod('oropayment/js/validator/credit-card-expiration-date');
            $.validator.loadMethod('oropayment/js/validator/credit-card-expiration-date-not-blank');

            this.$el = this.options._sourceElement;

            this.$form = this.$el.find(this.options.selectors.form);

            // To validate cvv, card number and expiration date at once on single page checkout
            this.$el.closest('form').on('submit', this._validateMicroform.bind(this));

            this.$el
                .on('change.' + this.cid, this.options.selectors.month, this._collectMonthDate.bind(this))
                .on('change.' + this.cid, this.options.selectors.year, this._collectYearDate.bind(this));

            this._loadFlexJsLibrary();
        },

        _loadFlexJsLibrary: function() {
            this._deferredInit();
            scriptjs(this.options.flexJsUrl, this._initializeFlexMicroform.bind(this));
        },

        _initializeFlexMicroform: function() {
            if (this.options.captureContext === null || this.microform !== null) {
                return;
            }

            const flex = new Flex(this.options.captureContext);
            this.microform = flex.microform();
            const number = this.microform.createField('number');
            const securityCode = this.microform.createField('securityCode');
            number.load('#' + this.options.dynamicSelectors.number);
            securityCode.load('#' + this.options.dynamicSelectors.securityCode);
            const self = this;

            number.on('focus', function() {
                self._microformFieldOnFocus('number');
            });
            number.on('blur', function() {
                self._microformFieldOnBlur('number');
            });
            number.on('change', function(data) {
                self._microformFieldOnChange(data, 'number');
            });

            securityCode.on('focus', function() {
                self._microformFieldOnFocus('securityCode');
            });
            securityCode.on('blur', function() {
                self._microformFieldOnBlur('securityCode');
            });
            securityCode.on('change', function(data) {
                self._microformFieldOnChange(data, 'securityCode');
            });

            this._resolveDeferredInit();
        },

        _microformFieldOnFocus: function(field) {
            const $fieldContainer = $('#' + this.options.dynamicSelectors[field]);

            if (this.microformErrors[field] !== null) {
                $fieldContainer.addClass('error');
            } else {
                $fieldContainer.addClass('focus-visible');
            }
        },

        _microformFieldOnBlur: function(field) {
            this._toogleMicroformValidationErrors(field);

            const $fieldContainer = $('#' + this.options.dynamicSelectors[field]);
            $fieldContainer.removeClass('focus-visible error');
        },

        _toogleMicroformValidationErrors: function(field) {
            const $fieldContainer = $('#' + this.options.dynamicSelectors[field]);
            let $fieldContainerWrapper = $fieldContainer;
            if (field === 'securityCode') {
                $fieldContainerWrapper = $('#' + this.options.dynamicSelectors[field] + '-wrapper');
            }
            let $errorContainer = $fieldContainerWrapper.siblings('.microform-validation-failed');

            if (this.microformErrors[field] !== null) {
                if (!$errorContainer.length) {
                    $errorContainer = $('<span class="microform-validation-failed validation-failed__icon"></span>');
                    $fieldContainerWrapper.after($errorContainer);
                }

                $fieldContainer.removeClass('focus-visible');
                $fieldContainer.addClass('error');

                const message = 'oro.cybersource.validation.' + field + '.' + this.microformErrors[field];
                $errorContainer.show().text(__(message));
            } else {
                if ($errorContainer.length) {
                    $errorContainer.hide();
                }

                $fieldContainer.removeClass('error');
                $fieldContainer.addClass('focus-visible');
            }
        },

        _microformFieldOnChange: function(data, field) {
            const $fieldContainer = $('#' + this.options.dynamicSelectors[field]);
            $fieldContainer.removeClass('focus-visible');

            if (data.empty) {
                this.microformErrors[field] = 'empty';
            } else if (!data.valid) {
                this.microformErrors[field] = 'invalid';
            } else {
                this.microformErrors[field] = null;
            }

            this._toogleMicroformValidationErrors(field);
        },

        _refreshPaymentMethod: function() {
            mediator.trigger('checkout:payment:method:refresh');
        },

        /**
         * @param {jQuery.Event} e
         */
        _collectMonthDate: function(e) {
            this.month = e.target.value;
        },

        /**
         * @param {jQuery.Event} e
         */
        _collectYearDate: function(e) {
            this.year = e.target.value;
        },

        _validateMicroform: function() {
            const self = this;
            let valid = true;
            _.each(this.microformErrors, function(item, key) {
                if (item !== null) {
                    self._toogleMicroformValidationErrors(key);
                    valid = false;
                }
            });

            return valid;
        },

        /**
         * @param {String} elementSelector
         */
        _validate: function(elementSelector) {
            let appendElement;
            if (elementSelector) {
                const element = this.$form.find(elementSelector);

                appendElement = element.clone();
            } else {
                appendElement = this.$form.clone();
            }

            const virtualForm = $('<form>');
            virtualForm.append(appendElement);

            const self = this;
            const validator = virtualForm.validate({
                ignore: '', // required to validate all fields in virtual form
                errorPlacement: function(error, element) {
                    const $el = self.$form.find('#' + $(element).attr('id'));
                    const parentWithValidation = $el.parents(self.options.selectors.validation);

                    $el.addClass('error');

                    if (parentWithValidation.length) {
                        error.appendTo(parentWithValidation.first());
                    } else {
                        error.appendTo($el.parent());
                    }
                }
            });

            virtualForm.find('select').each(function(index, item) {
                // set new select to value of old select
                // http://stackoverflow.com/questions/742810/clone-isnt-cloning-select-values
                $(item).val(self.$form.find('select').eq(index).val());
            });

            // Add validator to form
            $.data(virtualForm, 'validator', validator);

            let errors;

            if (elementSelector) {
                errors = this.$form.find(elementSelector).parent();
            } else {
                errors = this.$form;
            }

            errors.find(validator.settings.errorElement + '.' + validator.settings.errorClass).remove();
            errors.parent().find('.error').removeClass('error');

            return validator.form();
        },

        /**
         * @param {Object} eventData
         */
        _onPaymentMethodChanged: function(eventData) {
            if (eventData.paymentMethod === this.options.paymentMethod) {
                this._loadFlexJsLibrary();
                this._onCurrentPaymentMethodSelected();
            }
        },

        _onCurrentPaymentMethodSelected: function() {
            this._setGlobalPaymentValidate(this.paymentValidationRequiredComponentState);
        },

        /**
         * @param {Boolean} state
         */
        _setGlobalPaymentValidate: function(state) {
            this.paymentValidationRequiredComponentState = state;
            mediator.trigger('checkout:payment:validate:change', state);
        },

        /**
         * @param {Object} eventData
         */
        _beforeTransit: function(eventData) {
            if (eventData.data.paymentMethod === this.options.paymentMethod) {
                eventData.stopped = true;
                const valid = this._validate();

                if (this._validateMicroform() && valid) {
                    const options = {
                        expirationMonth: this.month,
                        expirationYear: this.year
                    };

                    const self = this;

                    this.microform.createToken(options, function(err, token) {
                        if (err) {
                            if (err.reason === 'CREATE_TOKEN_VALIDATION_FIELDS') {
                                _.each(err.details, function(item) {
                                    if (['securityCode', 'number'].includes(item.location)) {
                                        self.microformErrors[item.location] = 'invalid';
                                        self._toogleMicroformValidationErrors(item.location);
                                    } else {
                                        console.error(err);
                                    }
                                });
                            } else {
                                console.error(err);
                            }
                            mediator.execute(
                                'showFlashMessage',
                                'error',
                                __('oro.cybersource.create_token_error')
                            );
                        } else {
                            const additionalData = {
                                token: token
                            };

                            mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
                            eventData.resume();
                        }
                    });
                }
            }
        },

        _placeOrderResponse: function(eventData) {
            if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
                eventData.stopped = true;
                if (true === eventData.responseData.successful) {
                    mediator.execute('redirectTo', {url: eventData.responseData.successUrl}, {redirect: true});
                } else {
                    mediator.execute('redirectTo', {url: eventData.responseData.errorUrl}, {redirect: true});
                }
            }
        },

        dispose: function() {
            if (this.disposed || !this.disposable) {
                return;
            }

            this.$el.off('.' + this.cid);

            CreditCardComponent.__super__.dispose.call(this);
        },

        _beforeHideFilledForm: function() {
            this.disposable = false;
        },

        _beforeRestoreFilledForm: function() {
            if (this.disposable) {
                this.dispose();
            }
        },

        _removeFilledForm: function() {
            // Remove hidden form js component
            if (!this.disposable) {
                this.disposable = true;
                this.dispose();
            }
        }
    });

    return CreditCardComponent;
});
