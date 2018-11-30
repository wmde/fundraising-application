<template>
 <div id="faq" class="container">
 	<h2>{{ messages.page_title }}</h2>
 	<h5>{{ messages.page_subtitle }}</h5>
 	<div class="row">
 	<div class="col-xs-12 col-sm-8">
		<div class="form-shadow-wrap">
			<h2 class="title">{{ topicTitle }}</h2>
			<question v-for="(content, index) in page" v-bind:class="[ isOverview ? 'preview' : 'topic' ]"
					  v-on:question-opened="setOpenQuestionId( $event )"
					  :content="content"
					  :key="index"
					  :visible-question-id="openQuestionId">
			</question>
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
	<div class="sidebar col-xs-12 col-sm-4">
		<h5>{{ messages.about }}</h5>
		<ul>
			<li @click="populatePageByTopic( topic ); isOverview = false;"
				v-bind:class="[ 'link', 'underlined' ]" v-for="topic in content.topics">
				<a
					data-content-target="/page/Häufige Fragen"
					data-track-content 
					data-content-name="Topic" 
					:data-content-piece="topic.name">
				{{ topic.name }}
				</a>
			</li>
		</ul>
		<h5 class="second-menu">{{ messages.no_answer }}</h5>
		<ul class="second-menu">
			<li><a href="/contact/get-in-touch">{{ messages.contact_link }}</a></li>
		</ul>
	</div>
	</div>
</div>
</template>

<script>
import SearchBar from './components/SearchBar.vue';
import Question from './components/Question.vue';

export default {
	name: 'faq',
	components: {
		SearchBar,
		Question
	},
	props: [ 'messages', 'content' ],
	data() {
		return {
			isOverview: true,
			topicTitle: '',
			page: [],
			openQuestionId: ''
		};
	},
	mounted: function () {
		this.populatePageWithPreviewContent();
	},
	methods: {
		populatePageByTopic: function ( topic ) {
			this.page = this.content.questions.filter( question => question.topic.split( ',' ).indexOf( topic.id ) !== -1 );
			this.setTopicTitle( topic.name );
			this.setOpenQuestionId( '' );
		},
		populatePageWithPreviewContent: function () {
			this.setOpenQuestionId( '' );
			this.page = this.content.questions.filter( question => question.topic.split( ',' ).indexOf( '1' ) !== -1 );
			this.setTopicTitle( this.content.topics[ 0 ].name );
		},
		setTopicTitle: function ( name ) {
			this.topicTitle = name;
		},
		setOpenQuestionId: function ( id ) {
			this.openQuestionId = id;
		}
	}
};
</script>