export interface ErrorSummaryData {
	[key: string]: ErrorInfo
}

interface ErrorInfo {
	label: String,
	errorMessage: String,
}
