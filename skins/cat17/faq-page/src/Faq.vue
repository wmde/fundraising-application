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
					:data-content-piece="topic.name">
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
	data() {
		return {
			isPreview: true,
			page: {},
			preview: {
				name: 'preview',
				message: '<  Zurück zur Übersicht',
				content: [
					{
						question: 'Is Vue.js hard to use?',
						answer: 'Not if you know javascript! Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
						'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
						'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
						'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
						'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. ' +
						'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
					},
					{
						question: 'Is JavaScript hard to use?',
						answer: 'Not if you know another programming language! Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
						'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
						'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
						'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
						'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. ' +
						'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
					},
					{ 	question: 'Is programming hard to learn?',
						answer: 'Not really! Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
						'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
						'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
						'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
						'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. ' +
						'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
					}
				]
			},
			topics: [
				{
					name: 'Spendenbescheinigung',
					content: [
						{
							question: 'Spendenbescheinigung Question',
							answer: 'Spendenbescheinigung Answer ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						},
						{
							question: 'Spendenbescheinigung Question 2',
							answer: 'Spendenbescheinigung Answer 2 ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						}
					]
				},
				{
					name: 'Spendenbannern auf Wikipedia',
					content: [
						{
							question: 'Spendenbannern auf Wikipedia Question',
							answer: 'Spendenbannern auf Wikipedia Answer ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						},
						{
							question: 'Spendenbannern auf Wikipedia Question 2',
							answer: 'Spendenbannern auf Wikipedia Answer 2 ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						}
					]
				},
				{
					name: 'Wikimedia-Organisation',
					content: [
						{
							question: 'Wikimedia-Organisation Question',
							answer: 'Wikimedia-Organisation Answer ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						},
						{
							question: 'Wikimedia-Organisation Question 2',
							answer: 'Wikimedia-Organisation Answer 2 ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						}
					]
				},
				{
					name: 'Wikimedia-Projekte',
					content: [
						{
							question: 'Wikimedia-Projekte Question',
							answer: 'Wikimedia-Projekte Answer ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						},
						{
							question: 'Wikimedia-Projekte Question 2',
							answer: 'Wikimedia-Projekte Answer 2 ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						}
					]
				},
				{
					name: 'Spendenverwendung',
					content: [
						{
							question: 'Spendenverwendung Question',
							answer: 'Spendenverwendung Answer ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						},
						{
							question: 'Spendenverwendung Question 2',
							answer: 'Spendenverwendung Answer 2 ' +
							'Lorem ipsum dolor sit amet, consectetur adipiscing elit, ' +
							'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' +
							'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris ' +
							'nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ' +
							'reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. '
						}
					]
				}
			]
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
};
</script>

<style lang="scss">
  @import '../../src/sass/layouts/pages/faq.scss'
</style>
