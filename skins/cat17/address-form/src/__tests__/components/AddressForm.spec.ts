import { shallowMount, mount, createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'
import AddressForm from '../../AddressForm.vue'
import {Validity} from "@/types";

const localVue = createLocalVue();

localVue.use(Vuex)

function newTestProperties(overrides: Object) {
    return Object.assign(
        {
            addressToken: '',
            isCompany: true,
            messages: {},
            validateAddressURL: '/update-address',
            updateAddressURL: '/update-address/',
            countries: ['DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU']
        },
        overrides
    );
}

const defaultState = {
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
};

/* Basic sanity test */
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
        };
        store = new Vuex.Store({
            state: defaultState,
            actions,
            getters
        });
    });


    test('renders', () => {
        const wrapper = mount(AddressForm, {
            store,
            localVue,
            propsData: newTestProperties({ addressToken: 'bla' })
        });
        expect(typeof wrapper).toMatch('object')
    })
})