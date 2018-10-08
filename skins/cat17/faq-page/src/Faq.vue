<template>
 <div id="faq" class="container">
 	<div v-if="!isPreview" class="inline space-above">
 		<a @click="populatePageWithPreviewContent(); isPreview=true;"
	 		data-content-target="/page/Häufige Fragen" 
			data-track-content 
			data-content-name="Back to overview" 
			data-content-piece="Back to overview">
 		{{ preview.message }}
		</a>
 	</div>
 	<h2>Häufige Fragen</h2>
 	<h5>Antworten zu Ihren Fragen zum Spendenprozess, Wikimedia und Wikipedia</h5>
 	<div class="row">
 	<div class="col-xs-12 col-sm-9">
	  	<search-bar></search-bar>
		<div class="form-shadow-wrap">
			<h2 v-if="!isPreview" class="title space-below underlined"> {{ page.name }}</h2>
			<ul>
				<li v-for="content in page.content" v-bind:class="[ isPreview ? 'preview' : 'topic', 'underlined' ]">
					<question :content="content.question"></question>
					<answer :content="content.answer" :isPreview="isPreview"></answer>
				</li>
			</ul>
		</div>
		<footer>
			<h5>Sie konnten keine Antwort finden? Fragen Sie uns direkt!</h5>
			<p>
				Am effizientesten ist das <a>Fragestellen über das Kontaktformular.</a> Wir antworten dann per E-Mail.<br>
				Sie können auch:<br>
				eine E-Mail an <a>spenden@wikimedia.de</a> senden oder<br>
				unser Spendentelefon anrufen: 030/123456789
			</p>
		</footer>
	</div>
	<div class="sidebar col-xs-12 col-sm-3">
		<h5>Fragen und Antworten zu...</h5>
		<ul>
			<li v-for="topic in topics">
				<a @click="populatePageByTopic( topic ); isPreview = false;"
					data-content-target="/page/Häufige Fragen" 
					data-track-content 
					data-content-name="Topic" 
					data-content-piece="topic.name">
				{{ topic.name }}
				</a>
			</li>
		</ul>
		<h5>Keine Antwort gefunden?</h5>
		<ul>
			<li><a href="/contact/get-in-touch">Frage stellen via Kontaktformular</a></li>
			<li>Weitere <a>Kontaktmöglichkeiten</a></li>
		</ul>
	</div>
	</div>
</div>
</template>

<script>
import SearchBar from './components/SearchBar.vue'
import Question from './components/Question.vue'
import Answer from './components/Answer.vue'

export default {
	name: 'faq',
	components: {
		SearchBar,
		Question,
		Answer
	},
	data() {
		return {
			isPreview: true,
			page: {},
			content: faqContent
		};
	},
	mounted: function () {
		this.populatePageWithPreviewContent();
	},
	methods: {
		populatePageByTopic: function ( topic ) {
			this.page = topic;
		},
		populatePageWithPreviewContent: function () {
			this.page = this.preview;
		}
	}
}
</script>

<style lang="scss">
  @import '../../src/sass/layouts/pages/faq.scss'
</style>
