<template>
	<div id="app" class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-8 col-md-8">
				<div>
					<h1>{{ messages.use_of_funds_header }}</h1>
					<p>{{ messages.use_of_funds_description }}</p>
				</div>
				<organization-section v-for="org in content.organizations"
							  :title="org.title"
							  :overallAmount="org.overallAmount"
							  :description="org.description"
							  :funds="org.funds">
				</organization-section>
			</div>
			<div class="sidebar col-xs-12 col-sm-4 col-md-4">
				<ul class="list-menu list-unstyled">
					<li><a href="/" class="clickable">{{ messages.back_to_donation }}</a></li>
					<li class="itz-logo-item">
						<a :href="messages.content_pages_itz_link" target="_blank" :title="messages.content_pages_itz_title">
							<img :src="messages.content_pages_itz_logo" :alt="messages.content_pages_itz_title">
						</a>
					</li>
					<li>
						<a :href="content.organizations.wmde.url">{{ messages.year_plan_wmde }} {{ content.organizations.wmde.title }}</a>
					</li>
					<li>
						<a :href="content.organizations.wmf.url">{{ messages.year_plan_wmf }} {{ content.organizations.wmf.title }}</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
import OrganizationSection from './components/OrganizationSection.vue';

export default {
	name: 'app',
	components: {
		OrganizationSection
	},
	props: [ 'messages', 'content' ],
	data() {
		return {
			title: '',
			overallAmount: '',
			funds: {}
		};
	}
};
</script>
