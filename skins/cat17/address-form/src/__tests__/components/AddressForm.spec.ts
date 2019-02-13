import { shallowMount, mount, createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'
import AddressForm from '../../AddressForm.vue'
import { Validity } from "@/types";

const localVue = createLocalVue();

localVue.use(Vuex)

function newTestProperties(overrides: Object) {
    return Object.assign(
        {
            transport: newFakeTransport(),
            addressToken: '',
            isCompany: false,
            messages: {},
            validateAddressURL: '/update-address',
            updateAddressURL: '/update-address/',
            countries: ['DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU']
        },
        overrides
    );
}
function newFakeTransport() {
    return new Object({});
}
describe('AddressForm.vue', () => {
    let state: any
    let actions: any
    let store: any
    let getters: any

    beforeEach(() => {
        state = {
            isValidating: false,
            form: {
                salutation: Validity.INCOMPLETE,
                title: Validity.INCOMPLETE,
                firstName: Validity.INCOMPLETE,
                lastName: Validity.INCOMPLETE,
                companyName: Validity.INCOMPLETE,
                street: Validity.INCOMPLETE,
                postcode: Validity.INCOMPLETE,
                city: Validity.INCOMPLETE,
                country: Validity.VALID,
                addressType: Validity.VALID
            }
        },
        actions = {
            validateInput: jest.fn(),
            storeAddressFields: jest.fn()
        }
        getters = {
            validity: () => jest.fn(),
            invalidFields: jest.fn(),
            allFieldsAreValid: jest.fn()
        }
        store = new Vuex.Store({
            state,
            actions,
            getters
        });
    })

    it('renders', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ addressToken: 'bla' })
        });
        expect(typeof wrapper).toEqual('object')
    });

    it('passes property isCompany', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: true })
        });
        expect(wrapper.props('isCompany')).toBe(true);
    });

    it('shows company address form when isCompany is passed as true', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: true })
        });
        expect(wrapper.contains('#company-name')).toBe(true);
        expect(wrapper.contains('#first-name')).toBe(false);
        expect(wrapper.contains('#last-name')).toBe(false);
        expect(wrapper.contains('#salutation')).toBe(false);
        expect(wrapper.contains('#title')).toBe(false);
    });

    it('shows person address form when isCompany is passed as false', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: false })
        });
        expect(wrapper.contains('#company-name')).toBe(false);
        expect(wrapper.contains('#first-name')).toBe(true);
        expect(wrapper.contains('#last-name')).toBe(true);
        expect(wrapper.contains('#salutation')).toBe(true);
        expect(wrapper.contains('#title')).toBe(true);
    });

    it('does not show error messages on initial load of an empty form', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: false })
        });

        expect(wrapper.contains('span.error-text')).toBe(false);
    });

    it('dispatches validateInput action with the field\'s value on blur from an input field', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: false })
        });
        let input = wrapper.find('#first-name');
        input.setValue('test');
        input.trigger('blur');
        expect(actions.validateInput).toHaveBeenCalledWith(
            expect.anything(),
            {
                name: 'firstName',
                value: 'test',
                pattern: '^.+$',
                optionalField: false
            },
            undefined // empty "Root State"
        );
    });

    it('updates fieldErrors correctly according to the validation result', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: false })
        });

        let input = wrapper.find('#post-code');
        input.setValue('test');
        input.trigger('blur');
        expect(wrapper.fieldErrors.postcode).toBe(false);
    });

    it('saves user input to the respective part of formData', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: false })
        });
        let title = wrapper.find('#title');
        title.setValue('Prof. Dr.');
        let firstName = wrapper.find('#first-name');
        firstName.setValue('Testina');
        let lastName = wrapper.find('#last-name');
        lastName.setValue('von Testinson');

        expect(wrapper.vm.$data.formData.title.value).toBe('Prof. Dr.');
        expect(wrapper.vm.$data.formData.firstName.value).toBe('Testina');
        expect(wrapper.vm.$data.formData.lastName.value).toBe('von Testinson');
    });

    it('sets hidden field addressType "firma" for company form', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: true })
        });
        expect(wrapper.vm.$data.formData.addressType.value).toBe('firma');
    });

    it('sets hidden field addressType to "person" for person form', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: false })
        });
        expect(wrapper.vm.$data.formData.addressType.value).toBe('person');
    });

    it('dispatches storeAddressFields action with the correct values on submit', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: true })
        });
        let companyName = wrapper.find('#company-name');
        companyName.setValue('Testmedia Deutschland');
        let street = wrapper.find('#street');
        street.setValue('Testenhofen Ufer 23-24');
        let postCode = wrapper.find('#post-code');
        postCode.setValue('1234');
        let city = wrapper.find('#city');
        city.setValue('Testlin');
        let country = wrapper.find('#country');
        country.setValue('DE');
        let submit = wrapper.find('.btn-address-change')
        submit.trigger('click');

        expect(actions.storeAddressFields).toHaveBeenCalledWith(
            expect.anything(),
            {
                formData: {
                    addressType: {
                        name: 'addressType',
                        optionalField: false,
                        pattern: '',
                        value: 'firma'
                    },
                    city: {
                        name: 'city',
                        optionalField: false,
                        pattern: '^.+$',
                        value: 'Testlin'
                    },
                    companyName: {
                        name: 'companyName',
                        optionalField: false,
                        pattern: '^.+$',
                        value: 'Testmedia Deutschland'
                    },
                    country: {
                        name: 'country',
                        optionalField: false,
                        pattern: '',
                        value: 'DE'
                    },
                    firstName: {
                        name: 'firstName',
                        optionalField: true,
                        pattern: '^.+$',
                        value: ''
                    },
                    lastName: {
                        name: 'lastName',
                        optionalField: true,
                        pattern: '^.+$',
                        value: ''
                    },
                    postcode: {
                        name: 'postcode',
                        optionalField: false,
                        pattern: '[0-9]{4,5}$',
                        value: '1234'
                    },
                    salutation: {
                        name: 'salutation',
                        optionalField: true,
                        pattern: '^(Herr|Frau)$',
                        value: ''
                    },
                    street: {
                        name: 'street',
                        optionalField: false,
                        pattern: '^.+$',
                        value: 'Testenhofen Ufer 23-24'
                    },
                    title: {
                        name: 'title',
                        optionalField: true,
                        pattern: '',
                        value: ''
                    }
                },
                transport: {},
                validateAddressURL: '/update-address'
            },
            undefined // empty "Root State"
        );
    });

});
