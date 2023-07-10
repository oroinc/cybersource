class MicroformFieldStub {
    constructor(fieldName) {
        this.fieldName = fieldName;
        this.valueToResponseMap = {
            '': {empty: true, valid: false},
            '4111111111111111': {empty: false, valid: true},
            '321': {empty: false, valid: true}
        };
    }

    load(containerSelector) {
        const container = document.querySelector(containerSelector);
        container.innerHTML ='<input type="text" style="border: none;" name="microform_' + this.fieldName + '">';
        this.field = container.querySelector('input');
    }

    on(event, callback) {
        if (event === 'change') {
            const self = this;
            this.field.addEventListener('input', function(e) {
                const data = self.getData();
                callback(data);
            });
        } else {
            this.field.addEventListener(event, function(e) {
                callback();
            });
        }
    }

    getData() {
        let response = this.valueToResponseMap[this.field.value];

        if (response === undefined) {
            response = {empty: false, valid: false};
        }

        return response;
    }
}

class MicroformStub {
    constructor() {
        this.monthToTriggerGatewayError = '12';
        this.monthToGenerateInvalidToken = '11';
    }

    createField(fieldName) {
        return new MicroformFieldStub(fieldName);
    }

    createToken(options, callback) {
        if (options.expirationMonth === this.monthToTriggerGatewayError) {
            callback('Test error', 'flexible_form_token');
        } else if (options.expirationMonth === this.monthToGenerateInvalidToken) {
            callback(null, 'invalid_flexible_form_token');
        } else {
            callback(null, 'valid_flexible_form_token');
        }
    }
}

class Flex {
    microform(request, callback) {
        return new MicroformStub();
    }
}

window.Flex = Flex;
