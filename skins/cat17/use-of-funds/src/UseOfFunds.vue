<template>
	<div id="app" class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-8 col-md-8">
				<p class="org-title">
					{{ title }}<br>
					{{ overallAmount.replace(/ /g, '.') }}€
				</p>
				<p class="subtitle">{{ subtitle }}</p>
				<fund-section v-for="fund in funds"
						:title="fund.title"
						:amount="fund.amount"
						:description="fund.description"
						:width="calculateProgressBarWidth( fund.amount )">
				</fund-section>
			</div>
			<div class="sidebar col-xs-12 col-sm-4 col-md-4">
				<ul class="list-menu list-unstyled">
					<li><a href="/" class="clickable">{{ messages.back_to_donation }}</a></li>
					<li>
						<a :href="messages.content_pages_itz_link" target="_blank" :title="messages.content_pages_itz_title">
							<img :src="messages.content_pages_itz_logo" :alt="messages.content_pages_itz_title">
						</a>
					</li>
					<li v-for="org in content.organizations" @click="populatePageByOrg( org )" class="clickable">
						<a>{{ messages.year_plan }} {{ org.title }}</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
import FundSection from './components/FundSection.vue';

export default {
	name: 'app',
	components: {
		FundSection
	},
	props: [ 'content' ],
	data() {
		return {
			messages: {
				"year_plan": "Jahresplan",
				"back_to_donation": "Zurück zum Spendenformular",
				"content_pages_itz_link": "https://wikimedia.de/wiki/Transparenz",
				"content_pages_itz_logo": "../../../../vendor/wmde/fundraising-frontend-content/resources/de_DE/logo_itz.svg",
				"content_pages_itz_title": "Initiative Transparente Zivilgesellschaft"
			},
			title: '',
			overallAmount: '',
			funds: {}
		}
	},
	methods: {
		populatePageByOrg: function ( org ) {
			this.setPageTitle( org.title, org.description, org.overallAmount );
			this.funds = org.funds;
		},
		setPageTitle: function ( title, subtitle, overallAmount ) {
			this.title = title;
			this.subtitle = subtitle;
			this.overallAmount = overallAmount;
		},
		calculateProgressBarWidth: function ( amount ) {
			let castedOverAllAmount = Number( this.overallAmount.replace(/ /g, '') ),
				castedAmountNumber = Number( amount.replace(/ /g, '') ),
				barWidthPercentage = castedAmountNumber / castedOverAllAmount * 100;
			return barWidthPercentage.toString() + '%';
		}
	},
	mounted: function() {
		this.populatePageByOrg( this.content.organizations.wmde );
	}
}
</script>
