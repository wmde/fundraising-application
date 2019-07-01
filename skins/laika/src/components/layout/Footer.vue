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
							<bank-data></bank-data>
						</div>
					</div>
				</div>
				<div class="column is-one-third">
					<div class="level">
						<div class="level-item">
							<ul class="list-menu">
								<li v-for="( link, index ) in footerMenu" :key="index">
									<a :href="link.url" :key="link.id">
										{{ $t( 'footer_menu_' + link.id ) }}
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import BankData from '@/components/BankData.vue';
export default {
	name: 'Footer',
	components: { BankData },
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
