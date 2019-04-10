import { shallowMount } from '@vue/test-utils'
import Postal from '../../components/Postal.vue'

function newTestProperties(overrides: Object) {
    return Object.assign(
        {
            showError: {
                salutation: false,
                companyName: false,
                firstName: false,
                lastName: false,
                street: false,
                city: false,
                postcode: false
            },
            formData: {
                salutation: {
                    name: 'salutation',
                    value: '',
                    pattern: '^(Herr|Frau)$',
                    optionalField: false
                },
                title: {
                    name: 'title',
                    value: '',
                    pattern: '',
                    optionalField: true
                },
                companyName: {
                    name: 'companyName',
                    value: '',
                    pattern: '^.+$',
                    optionalField: true
                },
                firstName: {
                    name: 'firstName',
                    value: '',
                    pattern: '^.+$',
                    optionalField: false
                },
                lastName: {
                    name: 'lastName',
                    value: '',
                    pattern: '^.+$',
                    optionalField: false
                },
                street: {
                    name: 'street',
                    value: '',
                    pattern: '^.+$',
                    optionalField: false
                },
                city: {
                    name: 'city',
                    value: '',
                    pattern: '^.+$',
                    optionalField: false
                },
                postcode: {
                    name: 'postcode',
                    value: '',
                    pattern: '[0-9]{4,5}$',
                    optionalField: false
                },
                country: {
                    name: 'country',
                    value: 'DE',
                    pattern: '',
                    optionalField: false
                },
                addressType: {
                    name: 'addressType',
                    value: false ? 'firma' : 'person',
                    pattern: '',
                    optionalField: false
                }
            },
            validateInput: jest.fn(),
            messages: {},
            countries: []
        },
        overrides
    );
}

describe('AddressForm.vue', () => {
    it('shows street number warning if street field does not contain numbers', () => {
        const wrapper = shallowMount( Postal, {
            propsData: newTestProperties( {} )
        });
        let street = wrapper.find('#street');
        street.setValue('Testenhofen Ufer');
        street.trigger('blur');
        expect(wrapper.vm.$data.showWarning).toBe(true);
    });
});