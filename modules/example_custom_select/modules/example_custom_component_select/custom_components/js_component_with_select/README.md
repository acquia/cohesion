# Javascript custom component with custom select

This custom component demonstrates how a developer can create a custom component using javascript to fetch data from a data source and integrate a custom select.
The data source could be anything - hardcoded array, third party library or API call. For the purpose of this demo we are using Drupal route `example-custom-component-select.options` and the corresponding `ExampleCustomComponentSelectController::index()` controller.

All form values are stored in an object on a div's data attribute `data-ssa-custom-component`. This div will have a class
with the custom component ID (the file name of the yml file). See `js_component_with_select.custom_component.yml` for code details

This example also demonstrates that you can get field data and apply to CSS or you can attach your own CSS file
to the custom component. See `js_component_with_select.custom_component.yml` for how to add your CSS/JS file to a custom component.
