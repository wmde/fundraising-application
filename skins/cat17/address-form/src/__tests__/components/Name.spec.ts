import { shallowMount } from '@vue/test-utils'
import Name from '../../components/Name.vue'

function newTestProperties(overrides: Object) {
	return Object.assign(
		{
			showError: {
				salutation: false,
				companyName: false,
				firstName: false,
				lastName: false,
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

describe('Name.vue', () => {
	it('does not send any value when the new salutation field is blurred', () => {
		const props = newTestProperties( {} );
		const wrapper = shallowMount( Name, {
			propsData: props
		});
		let salutation = wrapper.find('#salutation');
		salutation.trigger('blur');
		expect(props.validateInput.mock.calls.length).toBe(0);
	});

	it('sends the value when the salutation field is blurred after a change', () => {
		const props = newTestProperties( {} );
		const wrapper = shallowMount( Name, {
			propsData: props
		});
		let salutation = wrapper.find('#salutation');
		salutation.setValue('Herr');
		salutation.trigger('blur');
		expect(props.validateInput.mock.calls.length).toBe(1);
		expect(props.validateInput.mock.calls[0][0].salutation.value).toBe('Herr');
	});

	it('sends the value when the empty salutation field is blurred from a previous value', () => {
		const props = newTestProperties( {} );
		props.formData.salutation.value = 'Frau';
		const wrapper = shallowMount( Name, {
			propsData: props
		});
		let salutation = wrapper.find('#salutation');
		salutation.setValue('');
		salutation.trigger('blur');
		expect(props.validateInput.mock.calls.length).toBe(1);
		expect(props.validateInput.mock.calls[0][0].salutation.value).toBe('');
	});
});