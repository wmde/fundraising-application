<template>
	<div class="footer has-background-footer">
		<div class="container">
			<div class="columns">
				<div class="column is-two-thirds">
					<div class="level">
						<div class="level-left">
							<div class="logo-footer">
								<a href="https://www.wikimedia.de/">
									<img :src="assetsPath + '/images/logo-vertical-wikimedia.svg'" alt="Wikimedia Deutschland">
								</a>
							</div>
						</div>
						<div class="level-item">
							<div class="bank-data-content">
								<p><strong>Kontoinhaber:</strong> Wikimedia Foerdergesellschaft</p>
								<p>
									<strong>{{ $t( 'iban')  }}:</strong><span class="spacer" v-for="ibanPartial in formatIban()">{{ ibanPartial }}</span>
								</p>
								<p><strong>{{ $t( 'bic')  }}:</strong> {{ $t( 'operator_account_bic')  }}</p>
								<p>Bank für Sozialwirtschaft</p>
								<p><strong>{{ $t( 'account_number')  }}:</strong> {{ $t( 'operator_account_number')  }}</p>
								<p><strong>{{ $t( 'blz')  }}:</strong> {{ $t( 'operator_account_blz')  }}</p>
							</div>
						</div>
					</div>
				</div>
				<div class="column is-one-third">
					<ul class="list-menu">
						<li v-for="link in footerMenu">
							<a :href="link.url" :key="link.id">
								<span>{{ $t( 'footer_menu_' + link.id ) }}</span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Footer',
	props: [ 'assetsPath' ],
	data: function () {
		return {
			'footerMenu': [
				{ id: 'contact', url: '/contact/get-in-touch' },
				{ id: 'imprint', url: '/page/Impressum' },
				{ id: 'data_protection', url: '/page/Datenschutz' },
				{ id: 'supporters_list', url: '/page/Unterst%C3%BCtzerliste' },
			],
		};
	},
	methods: {
		formatIban: function () {
			return this.$t( 'operator_account_iban' ).split( ' ' );
		},
	},
};
</script>

<style lang="scss">
	.bank-data-content {
		padding-top: 10px;
		p {
			line-height: 2.5em;
		}
	}
	.spacer {
		margin-left: 3px;
	}
	.logo-footer {
		text-align: center;
		img {
			width: 124px;
			height: 106px;
		}
	}
</style>
