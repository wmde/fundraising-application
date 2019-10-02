import { mount, createLocalVue, createWrapper } from '@vue/test-utils';

import { FeatureTogglePlugin } from '@/FeatureToggle';

const localVue = createLocalVue();
localVue.use( FeatureTogglePlugin, { activeFeatures: [ 'address.optional', 'skin.laika' ] } );

describe( 'FeatureToggle component', () => {

	it( 'does not render contents without slots', () => {
		const vm = new localVue( {
			render( h ) {
				return h( 'feature-toggle', {}, [
					h( 'div', 'Test' ),
				] );
			},
		} ).$mount();
		const v = createWrapper( vm );
		expect( v.vm.$el.childNodes.length ).toEqual( 0 );
	} );

	it( 'does not render slots for invisible features', () => {
		const vm = new localVue( {
			render( h ) {
				return h( 'feature-toggle', {}, [
					h( 'div', { slot: 'address.mandatory' }, 'Test' ),
				] );
			},
		} ).$mount();
		const v = createWrapper( vm );
		expect( v.vm.$el.childNodes.length ).toEqual( 0 );
	} );

	it( 'renders slot of visible features', () => {
		const vm = new localVue( {
			render( h ) {
				return h( 'feature-toggle', {}, [
					h( 'div', { slot: 'address.mandatory' }, 'Test mandatory address' ),
					h( 'div', { slot: 'address.optional' }, 'Test optional address' ),
				] );
			},
		} ).$mount();
		const v = createWrapper( vm );
		expect( v.vm.$el.childNodes.length ).toBe( 1 );
		expect( v.vm.$el.childNodes[ 0 ].textContent ).toBe( 'Test optional address' );
	} );

	it( 'can render multiple visible slots', () => {
		// when a render function returns multiple VNodes, you have to wrap it in a parent node for testing,
		// see https://stevenklambert.com/writing/unit-testing-vuejs-functional-component-multiple-root-nodes/
		const wrapper = mount( {
			template: `<div><feature-toggle>
							<div slot="skin.laika">Skin feature</div>
							<div slot="address.optional">Address feature</div>
						</feature-toggle></div>`,
		}, { localVue } );
		expect( wrapper.vm.$el.childNodes.length ).toBe( 2 );
		expect( wrapper.vm.$el.childNodes[ 0 ].textContent ).toBe( 'Skin feature' );
		expect( wrapper.vm.$el.childNodes[ 1 ].textContent ).toBe( 'Address feature' );
	} );

} );
