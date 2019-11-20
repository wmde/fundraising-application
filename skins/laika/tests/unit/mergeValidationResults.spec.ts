import { mergeValidationResults } from '@/merge_validation_results'

describe( 'mergeValidationResults', () => {
	it( 'Keeps OK state if all results are OK', () => {
		const okResult = { status: 'OK', messages: {} };
		expect( mergeValidationResults( [ okResult, okResult ] ) ).toEqual( okResult );
	} );

	it( 'Changes state to ERR if one of the results is not OK', () => {
		const okResult = { status: 'OK', messages: {} };
		const errResult = { status: 'ERR', messages: { subsystem1: 'Failure in the axionic deflector is unstable.' } };
		expect( mergeValidationResults( [ okResult, errResult, okResult ] ) ).toEqual( errResult );
	} );

	it( 'Collects all messages from ERR states', () => {
		const okResult = { status: 'OK', messages: { fyi: 'Fluctuations in the emission warp pod is not responding.' } };
		const errResult1 = { status: 'ERR', messages: {
			subsystem1: 'Failure in the axionic deflector is unstable.',
			subsystem99: 'Interference in the delphic rec deck.'
		} };
		const errResult2 = {
			status: 'ERR',
			messages: {
				subsystem1: 'Failure in the adaptation bearing.',
				subsystem2: 'Disturbances in the level-2 microreplication system.'
			}
		};
		expect( mergeValidationResults( [ okResult, errResult1, errResult2 ] ) ).toEqual( {
			status: 'ERR',
			messages: {
				subsystem1: 'Failure in the adaptation bearing.',
				subsystem2: 'Disturbances in the level-2 microreplication system.',
				subsystem99: 'Interference in the delphic rec deck.',
			}
		} );
	} );
} );