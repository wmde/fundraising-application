<template>
 <div id="faq" class="container">
 	<h2>Häufige Fragen</h2>
 	<h5>Antworten zu Ihren Fragen zum Spendenprozess, Wikimedia und Wikipedia</h5>
 	<div class="row">
 	<div class="col-xs-12 col-sm-9">
	  	<search-bar></search-bar>
		<div class="wrapper">
			<div class="form-shadow-wrap">
				<ol>
					<li v-for="c in content">
						<question :content="c.question"></question>
						<answer :content="c.answer"></answer>
					</li>
				</ol>
			</div>
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
			<li><a>Spendenbescheinigung</a></li>
			<li><a>Spendenbannern auf Wikipedia</a></li>
			<li><a>Wikimedia-Organisation</a></li>
			<li><a>Wikimedia-Projekte</a></li>
			<li><a>Spendenverwendung</a></li>
		</ul>
		<h5>Keine Antwort gefunden?</h5>
		<ul>
			<li>Frage stellen via Kontaktformular</li>
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
		};
	}
}
</script>
<style lang="scss">
  @import '../../src/sass/layouts/pages/faq.scss'
</style>

