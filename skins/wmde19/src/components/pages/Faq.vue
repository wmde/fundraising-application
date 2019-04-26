<template>
    <div id="faq" class="container">
        <h2>{{ $t('page_title') }}</h2>
        <h5>{{ $t('page_subtitle') }}</h5>
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
                    <h5>{{ $t('no_answer_found') }}</h5>
                    <p>
                        {{ $t('contact_way') }} <a href="/contact/get-in-touch">{{ $t('contact_link') }}</a> {{ $t('reply_by_email') }}<br>
                        {{ $t('you_can_too') }}<br>
                        {{ $t('send_email')}} <a :href="'mailto:' + $t('email_address')">{{ $t('email_address') }}</a> {{ $t('or') }}<br>
                        {{ $t('call_phone') }} {{ $t('phone') }}
                    </p>
                </footer>
            </div>
            <div class="sidebar">
                <h5>{{ $t('about') }}</h5>
                <ul>
                    <li @click="populatePageByTopic( topic ); isOverview = false;"
                        v-bind:class="[ 'link', 'underlined' ]"
                        v-for="topic in content.topics">
                        <a
                                data-content-target="/page/Häufige Fragen"
                                data-track-content
                                data-content-name="Topic"
                                :data-content-piece="topic.name">
                            {{ topic.name }}
                        </a>
                    </li>
                </ul>
                <h5 class="second-menu">{{ $t('no_answer') }}</h5>
                <ul class="second-menu">
                    <li><a href="/contact/get-in-touch">{{ $t('contact_link') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import Question from '../Question.vue';
import { FaqContent, Question as QuestionModel, Topic } from '../../view_models/faq';

@Component( {
	components: {
		Question,
	},
} )
export default class Faq extends Vue {
	@Prop() content!: FaqContent;

	isOverview = true;
	topicTitle = '';
	page: QuestionModel[] = [];
	openQuestionId = '';

	mounted() {
		this.populatePageOnInitialLoad();
	}

	populatePageByTopic( topic:Topic ) {
		this.page = this.content.questions.filter( question => question.topic.split( ',' ).indexOf( topic.id ) !== -1 );
		this.setTopicTitle( topic.name );
		this.setOpenQuestionId( '' );
	}

	populatePageOnInitialLoad() {
		this.setOpenQuestionId( '' );
		this.page = this.content.questions.filter( question => question.topic.split( ',' ).indexOf( '1' ) !== -1 );
		this.setTopicTitle( this.content.topics[ 0 ].name );
	}

	setTopicTitle( name:string ) {
		this.topicTitle = name;
	}

	setOpenQuestionId( id:string ) {
		this.openQuestionId = id;
	}
}
</script>
