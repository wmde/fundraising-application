<template>
 <div id="faq" class="container">
 	<div v-if="!isPreview" class="inline space-above">
 		<a @click="populatePageWithPreviewContent(); isPreview=true;"
	 		data-content-target="/page/Häufige Fragen" 
			data-track-content 
			data-content-name="Back to overview" 
			data-content-piece="Back to overview">
		{{ messages.back_link }}
		</a>
 	</div>
 	<h2 class="align-left">{{ messages.page_title }}</h2>
 	<h5 class="align-left">{{ messages.page_subtitle }}</h5>
 	<div class="row">
 	<div class="col-xs-12 col-sm-9">
	  	<search-bar :messageSearch="messages.search" class="align-left"></search-bar>
		<div class="form-shadow-wrap">
			<h2 v-if="!isPreview" class="title space-below underlined">{{ topicTitle }}</h2>
			<ul>
				<li v-for="content in page" v-bind:class="[ isPreview ? 'preview' : 'topic', 'underlined' ]">
					<question :content="content.question"></question>
					<answer :hiddenContent="content.hidden_text" :visibleContent="content.visible_text" :messages="messages" :isPreview="isPreview"></answer>
				</li>
			</ul>
		</div>
		<footer>
			<h5>{{ messages.no_answer_found }}</h5>
			<p>
				{{ messages.contact_way }} <a href="/contact/get-in-touch">{{ messages.contact_link }}</a> {{ messages.reply_by_email }}<br>
				{{ messages.you_can_too }}<br>
				{{ messages.send_email}} <a :href="'mailto:' + messages.email_address">{{ messages.email_address }}</a> {{ messages.or }}<br>
				{{ messages.call_phone }} {{ messages.phone }}
			</p>
		</footer>
	</div>
	<div class="sidebar col-xs-12 col-sm-3">
		<h5>{{ messages.about }}</h5>
		<ul>
			<li v-for="topic in content.topics">
				<a @click="populatePageByTopic( topic ); isPreview = false;"
					data-content-target="/page/Häufige Fragen" 
					data-track-content 
					data-content-name="Topic" 
					:data-content-piece="topic.name">
				{{ topic.name }}
				</a>
			</li>
		</ul>
		<h5>{{ messages.no_answer }}</h5>
		<ul>
			<li><a href="/contact/get-in-touch">{{ messages.contact_link }}</a></li>
			<li>{{ messages.further }} <a href="/contact/get-in-touch">{{ messages.contact_options }}</a></li>
		</ul>
	</div>
	</div>
</div>
</template>

<script>
import SearchBar from './components/SearchBar.vue';
import Question from './components/Question.vue';
import Answer from './components/Answer.vue';

export default {
	name: 'faq',
	components: {
		SearchBar,
		Question,
		Answer
	},
	props: [ 'messages', 'content' ],
	data() {
		return {
			isPreview: true,
			topicTitle: '',
			page: []
		};
	},
	mounted: function () {
		this.populatePageWithPreviewContent();
	},
	methods: {
		populatePageByTopic: function ( topic ) {
			this.page = this.content.questions.filter( question => question.topic.split( ',' ).indexOf( topic.id ) !== -1 );
			this.setTopicTitle( topic.name );
		},
		populatePageWithPreviewContent: function () {
			this.page = this.content.questions.filter( question => question.topic.split( ',' ).indexOf( '1' ) !== -1 );
		},
		setTopicTitle: function ( name ) {
			this.topicTitle = name;
		}
	}
};
</script>

<style lang="scss">
  @import '../../src/sass/layouts/pages/faq.scss'
</style>
