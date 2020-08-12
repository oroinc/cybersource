define(function(require) {
    'use strict';

    const _ = require('underscore');
    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const BaseComponent = require('oroui/js/app/components/base/component');

    const OrderReviewComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            paymentMethod: null
        },

        /**
         * @inheritDoc
         */
        constructor: function OrderReviewComponent(options) {
            OrderReviewComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            mediator.on('checkout:place-order:response', this.handleSubmit, this);
        },

        /**
         * @param {Object} eventData
         */
        handleSubmit: function(eventData) {
            if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
                eventData.stopped = true;

                const resolvedEventData = _.extend(
                    {
                        cyberSourceFormAction: '',
                        cyberSourceFormData: false
                    },
                    eventData.responseData
                );

                if (!eventData.responseData.cyberSourceFormAction) {
                    mediator.execute('redirectTo', {url: eventData.responseData.errorUrl}, {redirect: true});
                    return;
                }
                const submitFormData = resolvedEventData.cyberSourceFormData;
                this.postUrl(resolvedEventData.cyberSourceFormAction, submitFormData);
            }
        },

        /**
         * @param {String} formAction
         * @param {Object} data
         */
        postUrl: function(formAction, data) {
            const $form = $('<form action="' + formAction + '" method="POST" data-nohash="true">');
            _.each(data, function(value, key) {
                const $field = $('<input>')
                    .prop('type', 'hidden')
                    .prop('name', key)
                    .val(value);

                $form.append($field);
            });

            $('body').append($form);

            $form.submit();
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('checkout:place-order:response', this.handleSubmit, this);

            OrderReviewComponent.__super__.dispose.call(this);
        }
    });

    return OrderReviewComponent;
});
