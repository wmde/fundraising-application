import { mount, createLocalVue } from '@vue/test-utils';
import Vuex, { Store } from 'vuex';
import Email from '@/components/pages/donation_form/Email.vue';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { setEmail, setNewsletterOptIn } from '@/store/address/actionTypes';

const localVue = createLocalVue();
localVue.use(Vuex);

describe( 'Email', () => {

    it( 'checks if input has a valid email format on blur', () => {
        const wrapper = mount( Email, {
            store: createStore(),
            mocks: {
                $t: () => { },
            },
        });
        wrapper.find('#email').trigger('blur');
        expect( 'validateEmail' ).toBeHaveBeenCalled();
    } );

    it( 'shows an error if the entered email has an invalid format', () => {
        const wrapper = mount(Email, {
            store: createStore(),
            mocks: {
                $t: () => { },
            },
        });
        const email = wrapper.find('#email');
        email.setValue('abc');
        email.trigger('blur');
        const comp = wrapper.vm.$options.computed;
        if (comp !== undefined) {
            if (typeof comp.hasError === 'function') {
                expect(comp.hasError ).toBe(true);
            }
        }
    } );

    it(' does not show an error on initial render even though the field is empty', () => {
        const wrapper = mount(Email, {
            store: createStore(),
            mocks: {
                $t: () => { },
            },
        });
        const comp = wrapper.vm.$options.computed;
        if (comp !== undefined) {
            if (typeof comp.hasError === 'function') {
                expect(comp.hasError).toBe(false);
            }
        }
    });

    it( 'sends email to store if it is has valid format', () => {
        const wrapper = mount(Email, {
            store: createStore(),
            mocks: {
                $t: () => { },
            },
        });
        const store = wrapper.vm.$store;
        store.dispatch = jest.fn();
        const expectedAction = action(NS_PAYMENT, setEmail);
        const expectedPayload = {
            emailValue: 'abc@def.ghi',
        };
        const email = wrapper.find('#email');
        email.setValue('abc@def.ghi');
        email.trigger('blur');
        expect(store.dispatch).toBeCalledWith(expectedAction, expectedPayload);
    } );

    it( 'sends newsletter opt in choice to store', () => {
        const wrapper = mount(Email, {
            store: createStore(),
            mocks: {
                $t: () => { },
            },
        });
        const store = wrapper.vm.$store;
        store.dispatch = jest.fn();
        const expectedAction = action(NS_PAYMENT, setNewsletterOptIn);
        const expectedPayload = {
            newsletterOptIn: true,
        };
        const newsletter = wrapper.find('#newsletter');
        newsletter.trigger('click');
        expect(store.dispatch).toBeCalledWith(expectedAction, expectedPayload);

        newsletter.trigger('click');
        expect(store.dispatch).toBeCalledWith( expectedAction, { newsletterOptIn: false } );
    });

} )