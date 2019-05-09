// These types encapusaltes the types of JSOn responses that come from the server when calling a validation route

export interface SuccessResponse {
    status: 'OK';
}

export interface ErrorResponse {
    status: 'ERR';
    messages: Array<string>;
}

export type ValidationResponse = SuccessResponse | ErrorResponse;
