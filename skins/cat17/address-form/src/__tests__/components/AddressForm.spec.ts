import { shallowMount, mount, createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'
import AddressForm from '../../AddressForm.vue'

const localVue = createLocalVue();

localVue.use(Vuex)

function newTestProperties(overrides: Object) {
    return Object.assign(
        {
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

describe('AddressForm.vue', () => {
    let actions: any
    let store: any
    let getters: any

    beforeEach(() => {
        actions = {
            validateInput: jest.fn(),
            storeAddressFields: jest.fn()
        };
        getters = {
            validity: () => jest.fn(),
            invalidFields: jest.fn(),
            allFieldsAreValid: jest.fn()
        }
        store = new Vuex.Store({
            actions,
            getters
        });
    })

    it('Form renders', () => {
        const wrapper = mount(AddressForm, {
            propsData: newTestProperties({ addressToken: 'bla' })
        });
        expect(typeof wrapper).toEqual('object')
    });

    it('Property isCompany is passed', () => {
        const wrapper = mount(AddressForm, {
            propsData: newTestProperties({ isCompany: true })
        });
        expect(wrapper.props('isCompany')).toBe(true);
    });

    it('Company address form is shown when isCompany is passed as true', () => {
        const wrapper = mount(AddressForm, {
            propsData: newTestProperties({ isCompany: true })
        });
        expect(wrapper.contains('#company-name')).toBe(true);
        expect(wrapper.contains('#first-name')).toBe(false);
        expect(wrapper.contains('#last-name')).toBe(false);
        expect(wrapper.contains('#salutation')).toBe(false);
        expect(wrapper.contains('#title')).toBe(false);
    });

    it('Person address form is shown when isCompany is passed as false', () => {
        const wrapper = mount(AddressForm, {
            propsData: newTestProperties({ isCompany: false })
        });
        expect(wrapper.contains('#company-name')).toBe(false);
        expect(wrapper.contains('#first-name')).toBe(true);
        expect(wrapper.contains('#last-name')).toBe(true);
        expect(wrapper.contains('#salutation')).toBe(true);
        expect(wrapper.contains('#title')).toBe(true);
    });

    it('On blur from an input field validateInput is dispatched with the correct field value', () => {
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

    it('showError is updated correctly according to the validation result', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ isCompany: false })
        });
        let input = wrapper.find('#post-code');
        input.setValue('test');
        input.trigger('blur');
        expect(wrapper.vm.$data.showError.postcode).toBe(false);
    });

});