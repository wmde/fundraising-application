# How to create a validated interactive form

This document describes how to set up the JavaScript features of fundraising forms.

## Architecture

Forms use the [Redux][redux] library to handle events and store **internal state**. Internal state can be the form values,
the validity of those values, validation messages, which parts are displayed and hidden, etc. All the state is kept in
the **Store**. To change the state, an **Action** is dispatched to the store, where it is handled by **Reducers** -
[pure functions][pure_function] that have an initial state and the action as input and a changed state as output.

Each action is created with an **Action Creators** - a function that makes sure the action object is created correctly. 
Many actions have **payloads** - data that will be processed by the reducer functions. The payload given through the parameters of the action creator.

**Form components** are wrappers for form fields that send the form field value to the store with a `CHANGE_CONTENT` action 
and set the form field value when they receive form content updates from the store. This ensures that the store always 
has the current form field values and that form fields that are duplicated across form pages are in sync.  

The **view handler** classes are listening to changes in the state and update the HTML. They are more diverse than 
form components and do not dispatch actions.

For easier HTML updates both view handlers and components get a jQuery object passed in to their factory functions.

**Validators** are also listening to changes in the state. If a value changes, they call a validation function and
dispatch a "validation finished" action to the store, which then stores the validation result and validation error
messages with its reducers. The changed validation state and error messages may trigger view handlers that set CSS 
classes on validated fields and display the error messages. 

![Data flow in the architecture](architecture.svg)

### Reasons for this architecture
- Redux allows for a clear data flow and a central storage of form state instead of state storage that's tied directly to the DOM.
- We use view handlers and components instead of component libraries like [React][react] or [Vue][vue] because the view handlers can
  decouple HTML manipulation from the actual markup. When the markup changes, view handlers and components can be reused.

## Initializing the form

The library that provides the `WMDE` namespace object must be present (included with a `<script>` tag). See the README on how to generate the library file.

In the form code, insert the following initialization code. Its **TODO** parts will be explained and filled in the following sections.

```JavaScript

var initialFormValues = {% if initialFormValues %}{$ initialFormValues|json_encode|raw $}{% else %}{}{% endif %};

var store = WMDE.Store, // store object
    actions = WMDE.Actions; // action creators namespace
WMDE.StoreUpdates.connectComponentsToStore(
    [
        // TODO create components
    ],
    store
);

WMDE.StoreUpdates.connectValidatorsToStore(
    function ( initialValues ) {
        return [
            // TODO create validators
        ];
    },
    store,
    initialFormValues
 );
         
WMDE.StoreUpdates.connectViewHandlersToStore(
        [
            // TODO add view handler objects
        ],
        store
);

store.dispatch( actions.newInitializeContentAction( initialFormValues ) );

// TODO Connect user events and form values to actions
```

`initialFormValues` is an object with the initial form field values, as expected by the `form_content` reducer.
It can be filled server-side which is the reason why it's embedded in Twig template code that also returns a default
empty object hwen it's not set server side.

### Setting up form components

For every form input element (or group for elements in case of checkboxes and radio buttons), set up a form component 
in components array argument to the `initializeForm` function:
 
```JavaScript
WMDE.StoreUpdates.connectComponentsToStore(
    [
        WMDE.Components.createRadioComponent( store, $( '.payment-type-select' ), 'paymentType' ),
        WMDE.Components.createTextComponent( store, $( '.first-name' ), 'firstName' )
    ],
    store
);
```

Each `createXXXComponent` factory function has three arguments:
1. the store
2. a jQuery object for the form field. 
3. the key for storing the value in the global state (as part of the `formContent` object).

You can find all the available components in [`app/js/lib/form_components.js`](../app/js/lib/form_components.js).

### Setting up validation

Fill the validators function body with instances of `ValidationDispatcher` classes. A `ValidationDispatcher` calls a validation function with values from the `formContent` part of the store and dispatches an action to store the validation result. It only does this when the values from the store change.

The `createValidationDispatcher` factory function has four arguments:

1. The **validation function** to call. The function has to have one parameter - an object of the values to validate. The "validation function" argument can also be an object that has a `validate` method.
2. The action creator to call with the validation result.
3. An array of field names from the `formContent` part of the global store. The field names will be used to construct the value object for the validation function.
4. The `initialValues` parameter, which is passed on from the factory function

```JavaScript
WMDE.StoreUpdates.connectValidatorsToStore(
    function ( initialValues ) {
        return [
            WMDE.ReduxValidation.createValidationDispatcher(
                WMDE.FormValidation.createAmountValidator( '{$ basepath $}/validate-amount' ),
                WMDE.Actions.newFinishAmountValidationAction,
                [ 'amount', 'paymentType' ],
                initialValues
            )
        ];
    },
    store,
    initialFormValues
);
```

Actions names for storing validation results should follow the naming pattern of `FINISH_XXX_VALIDATION`, 
their action creation functions should follow the pattern `newFinishXXXValidationAction`

You can find available validation functions and classes in [`app/lib/form_validation.js`](../app/lib/form_validation.js)

### Setting up the view handlers

Fill out the view handlers array with new instances of view handlers

```JavaScript
WMDE.StoreUpdates.connectViewHandlersToStore(
    [
        {
            viewHandler: WMDE.View.createClearAmountHandler( $( '.amount-select' ), $( '.amount-input' ) ),
            stateKey: 'formContent'
        },
        {
            viewHandler: WMDE.View.createErrorBoxHandler( $( '#validation-errors' ), { amount: 'Betrag' } ),
            stateKey: 'validationMessages'
        }
    ],
    store
);
```

The `viewHandler` key of each object contains an instance of a view handler. The `stateKey` key of each object contains the key of the subsection of the global state that will be passed to the view handler.

For a list of available view handlers see the [`app/js/lib/view_handlers`](../app/js/lib/view_handlers) directory.

### Connecting user events and form values to actions
Add code that binds DOM events to Redux actions. 
Only connect DOM events that aren't handled by the form components. You can find all the available actions in
[`app/js/lib/actions.js`](../app/js/lib/actions.js).

```JavaScript
 $( '#donation-submit1 button' ).click( function () {
    store.dispatch( actions.newNextPageAction() );
} );
```

[redux]: http://redux.js.org/
[pure_function]: https://en.wikipedia.org/wiki/Pure_function
[react]: https://facebook.github.io/react/
[vue]: http://vuejs.org/
