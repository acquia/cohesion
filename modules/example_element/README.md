# Site Studio example element

This module is an example of how a developer can create a custom element. The element will be available to use in the
sidebar browser and will have a form and output defined by the developer.

## Demo

Enable this module from `/admin/modules`

1. Navigate to a Site Studio component or template
2. Open the sidebar browser, and scroll down to the Custom elements section
3. Drag "Example element" on to your layout canvas
4. Double click to edit the Example element
5. Fill out the form and click "Save"
6. Save the template/component and view the output.

## Creating your own element

The steps below explain how you can take this example module and create your own element that integrates seamlessly
with Site Studio.

Each element inside Site Studio has two components: the form and the template.

Data the user or site builder adds to the form is fed to a twig template to render the output. For internal Site Studio
elements, these templates exist on our API and are combined to make a single aggregated template that is used to drive
the output of entire nodes, pages, views, etc.

Your custom element is a bit different as it will be rendered at runtime instead of aggregated on the API.

You'll need to create a form definition and instead of a Site Studio template, you'll create a render function that will
render the output of the form at runtime. That render function*can*use a .twig template file to render the output but
this is not a requirement (this example_element module uses a local .twig template file for the output).

### 1. Creating your form.

Site Studio uses a custom form definition format that is not related to the Drupal form system. You have several field types
you can use and these will be displayed to the site builder in the "Settings" tab of the element.

**Add a new `CustomElement` plugin**

Create a new module and add a class under the CustomElement plugin namespace,
e.g. `[module_path]/src/Plugin/CustomElement/Generic.php`

The plugin class must extend `Drupal\cohesion_elements\CustomElementPluginBase`
and be annotated with `CustomElement`, for example:

```
namespace Drupal\module_name\Plugin\CustomElement;

use Drupal\cohesion_elements\CustomElementPluginBase;

/**
 * Generic HTML element plugin for Site Studio.
 *
 * @CustomElement(
 *   id = "generic_html_tag",
 *   label = @Translation("Generic HTML tag with text content")
 * )
 */
class Generic extends CustomElementPluginBase {
```

The `label` key is what will appear in the "Element" select list on the Custom
element form. Both `id` and `label` are required.

The class must implement both `getFields()` and `render()` methods. `render()`
is discussed below in step #2.

`getFields()` returns an array defining the form structure. Each entry should be
a keyed array in the following format:

```
public function getFields() {
  return [
    'tag' => [
      'htmlClass' => 'ssa-grid-col-xs-12',
      'type' => 'select',
      'title' => 'HTML tag',
      'nullOption' => false,
      'options' => [
        'p' => 'p',
        'span' => 'span',
        'div' => 'div',
      ]
    ],
    'text' => [
      'htmlClass' => 'ssa-grid-col-xs-12',
      'title' => 'Text content',
      'type' => 'textfield',
      'placeholder' => 'e.g. Site Studio is great',
      'defaultValue' => 'This value will be pre-filled',
      'required' => true,
      'validationMessage' => 'The text content field is required',
    ],
  ];
}
```

Available field types are:

`'textfield'` `'select'` `'checkbox'` `'image'` `'textarea'` `'wysiwyg'`

Depending on the type of field you select, you will be required to add additional fields.

See the examples in `src/Plugin/CustomElement/Example.php` for details.


### 2. Creating your render method.

Your render method is passed four parameters and should return a render array:

```
public function render($element_settings, $element_markup, $element_class, $element_context = [])
{
  // Render the element.
  return [
    '#theme' => 'example_element',
    '#template' => 'example-element-template',
    '#elementSettings' => $element_settings,
    '#elementMarkup' => $element_markup,
    '#elementContext' => $element_context,
    '#elementClass' => $element_class,
];
}
```

`$settings` is an array containing the form data input by the user/site builder along with the element name.

Note, the element name is provided as a convenience in case you want to use the same render function for multiple
elements.

```
(
   "element" => "module_example_1",
   "mycheckboxfield" => true,
   "myothercheckboxfield" => true,
   "mytextfield" => "Test 'data'.",
   "myselectfield" => "option2",
   "mytextareafield" => "Test data 2.",
   "mywysiwygfield"= > "<p>Test data 3.</p>n"
)
```

`$markup` is an array containing data added to the "Markup" tab.

Example:

```
(
  [attributes] => Array
    (
      [0] => Array
        (
          [attribute] => "data-something"
          [value] => "somevalue"
        )
    )
  [prefix] => "someprefix"
  [suffix] => "somesuffix"
)
```

`$class` is an string containing the class that targets your element. Anything added tot he style stab will be build
into the Site Studio stylesheets and available under this class name.

It has the format: 'coh-ce-xxxxxxx' with NO preceding dot/period character.

`$element_context` contains an array describing the structure of the context visibility tab. It is up to you to handle
the logic of showing/hiding based on this data (typically you would attach a `#cache` section to your render array).


Your function should return a renderable array. An example of that (using a .twig template) can be found here:

https://www.drupal.org/docs/8/theming/twig/create-custom-twig-templates-from-custom-module

## Custom element container

Custom elements can be a container element, please see `src/Plugin/CustomElement/ContainerExample.php` for an example of how to implement a custom element as a container.
