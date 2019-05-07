const axios = {
	default: jest.fn(),
	post: () => new Promise( res => res( { status: 'OK' } ) )
};

export default axios