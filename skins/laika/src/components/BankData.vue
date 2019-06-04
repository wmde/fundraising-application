<template>
	<div class="bank-data-content">
		<p><strong>{{ $t( 'account_owner' ) }}:</strong> Wikimedia Foerdergesellschaft</p>
		<p>
			<strong>{{ $t( 'iban')  }}:</strong>
			<span
					class="spacer"
					v-for="( ibanPartial, index ) in formatIban()"
					:key="index">{{ ibanPartial }}</span>
		</p>
		<p><strong>{{ $t( 'bic')  }}:</strong> {{ $t( 'operator_account_bic')  }}</p>
		<p>Bank für Sozialwirtschaft</p>
		<p><strong>{{ $t( 'account_number')  }}:</strong> {{ $t( 'operator_account_number')  }}</p>
		<p><strong>{{ $t( 'blz')  }}:</strong> {{ $t( 'operator_account_blz')  }}</p>
		<p v-if="bankTransferCode"><strong>{{ $t( 'transfer_code' ) }}:</strong> {{ bankTransferCode }}</p>
	</div>
</template>
<script>
export default {
	name: 'BankData',
	props: [
		'bankTransferCode',
	],
	methods: {
		formatIban: function () {
			return this.$t( 'operator_account_iban' ).split( ' ' );
		},
	},
};
</script>
