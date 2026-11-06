# Site Studio example custom select

This module is an example of how a developer can create a custom option list for use in Site Studio `Select` fields.

Two new options have been added for the Select form field in component forms that can be used to retrieve JSON values in
a compatible format:

* **Options from external data source:** An externally accessible URL and/or a dedicated internal Drupal endpoint
* **Options from custom function:** A javascript function attached to the page

## Demo

Enable this module from `/admin/modules`

### Drupal route
1. Create a new Component by navigating to: `admin/cohesion/components/components/add`
2. Add a `Select` field to the Component form builder
3. Double-click the new Select field and choose `Options from external data source` as the `Type`
4. Enter `/sitestudio/select-options` to use the Drupal route supplied by this example module
5. Observe the dynamic values supplied by this endpoint in the `Default value` options.

### Javascript function
1. Create a new Component by navigating to: `admin/cohesion/components/components/add`
2. Add a `Select` field to the Component form builder
3. Double-click the new Select field and choose `Options from custom function` as the `Type`
4. Enter `exampleGetOptions1` to use the Javascript function supplied by this example module
5. Observe the dynamic values supplied by this endpoint in the `Default value` options.

## Example code

This module provides an example of both the Drupal and Javascript implementations of this feature.

### Drupal route
* [Drupal route](example_custom_select.services.yml) - defines an endpoint for returning JSON.
* [Controller](src/Controller/ExampleCustomSelectController.php) - returns JSON data.

### Javascript function
* [Javascript library definition](example_custom_select.libraries.yml) - defines a custom JS library.
* [Javascript library](js/example_custom_select.js) - return JSON data.
* [module file](example_custom_select.module) - attaches library during entity create/edit.
* [Service definition](example_custom_select.services.yml) - defines the EventSubscriber service.
* [Event Subscriber](src/EventSubscriber/ExampleCustomSelectSubscriber.php) - attaches library during Page Builder load.

**Note**: an [Event subscriber](https://www.drupal.org/docs/develop/creating-modules/subscribe-to-and-dispatch-events) must be utilised to ensure
a custom Javascript library is attached during Page Builder interaction.

## JSON format

When writing a function to retrieve options for the Select form element, it is crucial to adhere to specific guidelines
to ensure consistency and compatibility. This documentation provides a set of rules and examples for creating a valid
options retrieval function.

### Rules for a Valid Options Retrieval Function

1. Return Type:

   * The function must return an array of objects representing options.
   * Each object in the array should have a label property and a value property.

  ```
  // Valid Example
  [
    { label: 'Option 1', value: 'option1' },
    { label: 'Option 2', value: 'option2' },
    // ...
  ]
  ```

2. Optional Grouping:

   * Optionally, an object can have a group property to categorize options.

  ```
  // Valid Example
  [
    { label: 'Option 1', value: 'option1', group: 'Group A' },
    { label: 'Option 2', value: 'option2', group: 'Group B' },
    // ...
  ]
  ```

3. Function Structure:

   * The options retrieval function must be a callable function and the function must be globally available (on the Window object.)

  ```
  // Valid Example
  const validOptionsFunction = function () {
    return [
      { label: 'Option 1', value: 'option1' },
      { label: 'Option 2', value: 'option2' },
      // ...
    ];
  };
  window.getOptions = validOptionsFunction;
  ```

## Examples

### Valid Examples

1. Using a Promise (asynchronous):

  ```
  window.exampleGetOptions = async function () {
    return new Promise((resolve) => {
     setTimeout(() => {
       resolve([
         { label: 'Foo', value: 'foo' },
         { label: 'Bar', value: 'bar' },
         { label: 'Wut', value: 'wut', group: 'Other' },
       ]);
      }, 1000); // Simulate a delay of 1 second
    });
  };
  ```

2. Using a synchronous function:

  ```
  window.exampleGetOptions1 = function () {
    return [
     { label: 'Foo', value: 'foo' },
     { label: 'Bar', value: 'bar' },
     { label: 'Wut', value: 'wut', group: 'Other' },
    ];
  };
  ```

### Invalid Examples

1. Not returning an array:

  ```
  window.exampleGetOptions2 = function () {
     return 'This is not an array of options.';
  };
  ```

2. Not being a callable function:

  ```
  window.exampleGetOptions3 = [
    { label: 'this is not a function', value: 'this is not a function' },
  ];
  ```

3. Invalid object properties:

  ```
  window.exampleGetOptions4 = async function () {
    return [
      { label: 'Foo', value: 'foo' },
      { label: 'Bar', value: 'bar' },
      { label: 'Wut', foo: 'Other' }, // Invalid property 'foo'
    ];
  };
  ```

## Creating a valid custom URL

### Overview
When retrieving options from a URL it is crucial that the data returned from the URL is in the correct format and is
returned as JSON data.

1. Return Type:

   * The URL must return an array of objects representing options.
   * Each object in the array should have a label property and a value property.
  ```
  [
    { "label": "Option 1", "value": "option1" },
    { "label": "Option 2", "value": "option2" }
  ]
  ```

2. Optional Grouping:

   * Optionally, an object can have a group property to categorize options.
  ```
  [
    { "label": "Option 1", "value": "option1", "group": "Group A" },
    { "label": "Option 2", "value": "option2", "group": "Group B" }
  ]
  ```
