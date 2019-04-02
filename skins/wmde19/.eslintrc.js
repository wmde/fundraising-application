module.exports = {
	root: true,
	env: {
		node: true,
		es6: true,
	},
	plugins: [
		'@typescript-eslint',
	],
	'extends': [
		'wikimedia',

	],
	rules: {
		'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'off',
		'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',

		// problematic in TypeScript / ES6
		'no-unused-vars': 'off',
		// TODO uncomment when https://github.com/typescript-eslint/typescript-eslint/issues/363 is fixed
		// '@typescript-eslint/no-unused-vars': 'error',
		'one-var': 'off',
		'no-undef': 'off',

		// diverging from Wikimedia rule set
		'max-len': [ 'error', 120 ],
		'comma-dangle': [ 'error', 'always-multiline' ],
		'operator-linebreak': 'off',
		'quote-props': 'off',
		'valid-jsdoc': 'off',
	},
	'parser': 'vue-eslint-parser',
	'parserOptions': {
		'parser': '@typescript-eslint/parser',
		'sourceType': 'module',
		'ecmaVersion': 7,
		'ecmaFeatures': {
			'modules': true,
		},
	},
};
