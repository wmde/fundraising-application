import { shallowMount } from '@vue/test-utils'
import AddressForm from '../../AddressForm.vue'

function newTestProperties(overrides: Object) {
    return Object.assign(
        {
            addressToken: '',
            isCompany: true,
            messages: {},
            validateAddressURL: '/update-address',
            updateAddressURL: '/update-address/',
            countries: {
                type: Array,
                default: function () {
                    return ['DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU'];
                }
            }
        },
        overrides
    );
}

/* Vue component dependencies */
const mocks = {
    store() {
        return { state: {} };
    }
};

/* Basic sanity test */
describe('AddressForm.vue', () => {
    test('renders', () => {
        const wrapper = shallowMount(AddressForm, {
            mocks,
            propsData: newTestProperties({ addressToken: 'bla' })
        });
        expect(typeof wrapper).toMatch('object')
    })
})