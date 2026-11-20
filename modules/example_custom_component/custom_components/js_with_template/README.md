# Javascript custom component with html template

This custom component demonstrates how a developer can create a custom component by providing an HTML template
to work with.

The html template needs to be declared in the `.yml` file (see `js_with_template.custom_component.yml`).

All form values are stored in an object on a div's data attribute `data-ssa-custom-component`. This div will have a class
with the custom component ID (the file name of the yml file). See js_component.js for code details

Dropzones are stored within the main div in a `<template>` and can be fetched by a data attribute which contains the dropzone's
uuid (see `js-template.js`)
