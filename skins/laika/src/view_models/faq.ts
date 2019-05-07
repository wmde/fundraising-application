export interface FaqData {
	openQuestionId: string
}

export interface QuestionModel {
	question : string,
	visibleText : string,
	topic : string,
}

export interface Topic {
	id : string,
	name : string,
}

export interface FaqContent {
	topics : Topic[],
	questions : QuestionModel[],
}

/**
 * Convert from json format (with snake case) to proper typescript format in interfaces ( camel case )
 */
export function faqContentFromObject( obj: any ): FaqContent {
	return {
		topics: obj.topics,
		questions: obj.questions.map( ( rawQuestion: any ) => {
			return {
				question: rawQuestion.question,
				visibleText: rawQuestion.visible_text,
				topic: rawQuestion.topic,
			};
		} ),
	};
}
