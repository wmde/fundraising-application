import { shallowMount, mount, createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'
import AddressForm from '../../AddressForm.vue'
import {Validity} from "@/lib/validation_states";

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
        salutation: {
            dataEntered: false,
            isValid: Validity.INCOMPLETE
        },
        title: {
            dataEntered: false,
            isValid: Validity.INCOMPLETE
        },
        firstName: {
            dataEntered: false,
            isValid: Validity.INCOMPLETE
        },
        lastName: {
            dataEntered: false,
            isValid: Validity.INCOMPLETE
        },
        companyName: {
            dataEntered: false,
            isValid: Validity.INCOMPLETE
        },
        street: {
            dataEntered: false,
            isValid: Validity.INCOMPLETE
        },
        postcode: {
            dataEntered: false,
            isValid: Validity.INCOMPLETE
        },
        city: {
            dataEntered: false,
            isValid: Validity.INCOMPLETE
        },
        country: {
            dataEntered: false,
            isValid: Validity.VALID
        },
        addressType: {
            dataEntered: false,
            isValid: Validity.VALID
        }
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