# Release notes

## 6.1.0

### Navigating between Style Guide Manager tabs causes them to move around

#### What is it?

When selecting a different sibling `Tab item` in a Style Guide Manager form, it causes the selected tab to move to the first position.

This should only happen when `Responsive mode` is enabled on the parent `Tab container` (on by default).

#### What impact will there be?

Tab items will stay in place if responsive mode is disabled.

**Note: Tabs will not stack if responsive mode is disabled.**

### Cohesion Component machine name support

#### What is it?

When adding form fields to Cohesion Components you will now be required to specify a machine name for them. When entering a title/label for your field, the machine name will be auto-generated but can be edited manually if you prefer.

This machine name will then be used to link the field to elements in your component.

The syntax for tokenizing fields has changed slightly. Previously your input would be linked by entering `[Field 1]`, `[Field 2]` etc. The new syntax is `[field.` followed by the machine name you specify e.g. `[field.my-machine-name]`.

As a result of this change it is now possible to add the token to fields on the layout canvas before you create the fields on the component form. Previously you had to add fields first.


#### What impact will there be?

Workflow for the creation of components has changed. You must set/use a unique machine name for every field you add. Existing components will be updated with an auto-generated machine name based on the field title.

As an example a field with the label "Paragraph padding top" would be assigned a machine name of `paragraph-padding-top`.

#### What actions do I need to take?

**A rebuild is required to ensure existing form fields are updated.**

#### Are there any risks I should be aware of?

None.

### Prefixes in the Style tree now support all selector types

#### What is it?

Adds the ability to use all CSS selectors types in the style tree when adding a Prefix selector. 

This includes Class, ID and Attribute CSS selectors.

#### What impact will there be? 

Any prefix CSS Class selectors already added in the Style tree without the CSS class selector (`.`) will automatically be prefixed with a `.`

#### What actions do I need to take? 

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Drupal core media library compatibility 

#### What is it?

Adds the ability to use the core media library to browse for media entities on the image, picture and entity browser elements.

A new option for media library is available in the image browser system settings and within the entity browser element and field settings.

#### What impact will there be?

You can now use the core media library within supported Cohesion elements.

#### What actions do I need to take?

The media library module will need to be installed to use within Cohesion.

#### Are there any risks I should be aware of?

None.

### Cohesion Import batch failing when used inside hook_update

#### What is it?

Fixes a bug where cohesion_base_styles_process_batch $context param was typed as array which prevented using Object extending ArrayObject, more specifically DrushBatchContext

#### What impact will there be?

You can now use the Cohesion import batch process inside an hook_update

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

None

### Blocks in the block element select list are now alphabetical

#### What is it?

The blocks within the block element select list are now alphabetical based on the block label.

#### What impact will there be?

The blocks will now be ordered alphabetically and easier to find in the select list.

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

None

### Cohesion "Component content" entity now implements a view mode

#### What is it?

Previously, the Cohesion component content entity did not implement a Drupal core view mode. This caused some issues with modules attempting to render this entity programmatically.

This issue has been resolved by implementing a default view mode for this entity.

#### What impact will there be?

Third party modules that attempt to render the Cohesion component content entity programmatically will be able to do so via its default view mode.
This includes Acquia Lift via Acquia Content Hub 2+

The display settings for this entity have not been enabled, so it will not be possible to access the display settings or add additional view modes for this entity. This fix is hidden from the point of view of the site administrator.

#### What actions do I need to take?

Performing a `drush updb` as part of the standard upgrade will deploy this fix.

The fix ID `8800` and will appear as "Add Cohesion view mode to Component Content entity type"

#### Are there any risks I should be aware of?

None

### Tokenizing Opacity in Styles result in an invalid value

#### What is it?

When tokenizing Opacity on Base, Custom or Element styles it would return an incorrect value.

#### What impact will there be?

You can now tokenie Opacity in Base, Custom or Element styles

#### What actions do I need to take?

If you have already tokenized Opacity on a Base, Custom or Element  style you will need to run `drush cohesion:rebuild` or resave the style.

#### Are there any risks I should be aware of?

No.

### Entity browser Element and Component now includes the ability to browser with a Typeahead

#### What is it?

Adds the ability when using the Entity browser element or Entity browser component field to browse Entities using a Typeahead.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

No.

### Component/SGM forms - breakpoint icon support

#### What is it?

When adding breakpoint-specific fields to component or SGM forms you can now display breakpoint icons in the form, on field groups or tabs.

#### What impact will there be?

- The `Field group` and `Tab item` form elements have a new setting called `Breakpoint icon`, a select field that provides options for multiple breakpoint options. The default setting is `None`.

- The `Tab container` form element has a new setting called `Responsive mode`, a toggle which when enabled causes tabs to collapse into a dropdown menu with an ellipsis button (below a screen width of `768px`). The default setting is `ON`.

#### What actions do I need to take?

When enabling breakpoint icons on tab items:
- Tab text is visually hidden. Make sure it is still meaningful for screen reader accessibility.
- It is recommended you disable responsive mode on the tab container when four or less breakpoint tabs are defined, as these tabs are smaller and don't need to stack.

**Note: A rebuild is required to ensure existing form fields are updated.**

#### Are there any risks I should be aware of?

Existing tab items will not be responsive until a rebuild is completed.

### Stream wrapper cohesion:// deprecated

#### What is it?

We have deprecated the use of the cohesion:// stream wrapper in all Cohesion modules

#### What impact will there be?

There will be no part of Cohesion using the cohesion:// stream wrapper and it will now use the public:// one, storing everything in public://cohesion
There is no impact on existing site or sync package from previous version on Cohesion

#### What actions do I need to take?

`drush updb`
`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

If you were using the cohesion:// stream wrapper in a custom module you should plan to use public://cohesion instead as it will be removed in future release

### Component content throwing an error when translating

#### What is it?

Fixes a bug when saving a new translation of a component content the page was returning an error

#### What impact will there be?

You can now use component content with translations

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

No

### Blocks in the block element can now be filtered by theme

#### What is it?

A new select option is now available in the block element that allows a site builder to filter blocks by theme. The blocks select list is then filtered to show only blocks in the selected theme.
By default, the "All themes" option will be set, and existing blocks will continue to work.

#### What impact will there be?

Blocks can be filtered by theme and easier to find in the block select list.

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

No

### Bugfix: Content moderation for Cohesion component in-context editing

#### What is it?

Previously, when editing a component on a content entity via the front end in-context editor, Cohesion was applying changes to the component to the latest revision regardless of which entity was being viewed and edited.

#### What impact will there be?

It's now possible to switch between moderation states on the front end (using moderation toolbar for example) and edit Cohesion component field data for that specific revision.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Content templates list, only show view modes that have been enabled in "manage display"

#### What is it?

In the Cohesion content templates list only view modes that have been enabled for that bundle in the "manage display" will appear in the list.

#### What impact will there be?

If a view mode has been enabled for a bundle in the "manage display" a content template can be created for that view mode.

On existing sites where the view mode might not be enabled, but a template was created, the existing template will appear in the list.

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

No

### `Field group` form element - new `Enable padding` toggle setting

#### What is it?

When adding a `Field group` field to a component or SGM form, there is now a new toggle option called `Enable padding`. This option removes the padding and background color of the field group.

Previously, if the `Show heading` toggle was off, the field group heading, padding and background color were all removed together.

With some form designs it's desirable to remove the heading or padding separately.

#### What impact will there be?

Greater flexibility with component and SGM form design.

#### What actions do I need to take?

A rebuild is required so that existing field groups are updated with the new settings, to ensure no visual change to existing forms.

#### Are there any risks I should be aware of?

If a rebuild is not completed, forms that have field groups with `Show heading` disabled will be padded and have a background color, as per a default field group added to a form. The heading will still be disabled.

### `Help text` form field improvements

#### What is it?

You can now customise your component and SGM help text in the following ways:

- set a type (help or warning style)
- set/unset close button
- add formatting to help text to output headings, ordered lists, unordered lists and links.

When enabled, the close button will show in the top-right corner of the help dialog. Clicking this will remove the help text and set a cookie to stop the help text showing when the component or SGM form is opened again.

**Note: the browser cache will need to be cleared to clear the cookie and restore the help text.**

Help text formatting supports all [CommonMark](https://commonmark.org/) markdown syntax.

#### What impact will there be?

Greater flexibility with help text on component and style guide forms.

#### What actions do I need to take?

None - existing help text will only need to be updated if the new options are needed.

#### Are there any risks I should be aware of?

No

### Disabled styles no longer appear as a blank option in the Custom styles select

#### What is it?

Fixes an issue when disabling a Custom style it would appear blank in the Custom select where it had been used.

#### What impact will there be?

If a style has been selected in the Custom styles list and is subsequently then disabled, it will now show _"Selected style is disabled."_

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

No

### Menu button element - button text

#### What is it?

A new setting on the `Menu button` element that allows button text to be specified.

This generates a child `<span>` element for the button with a class of `coh-menu-button-link-text`. The span has been added around the text to provide flexibility with CSS styling.

#### What impact will there be?

The form interface for the `Menu button` element will have an extra section called `Button text` with a text input.

#### What actions do I need to take?

For existing `Menu button` elements that have been added to a menu template, the new setting will need to be enabled in `Properties > Settings > Button text`.

New elements added will have the setting in the form by default.

#### Are there any risks I should be aware of?

No, as a new option existing settings will be unaffected.

### Cohesion no longer supports Drush 8

#### What is is?

To support the upcoming release of Drupal 9, Cohesion has dropped support from Drush version 8.

#### What impact will there be?

If you use Drush 8 and Cohesion together, the Cohesion drush commands will no longer work.

#### What actions do I need to take?

Upgrade to Drush version 9 or higher wherever you use Cohesion Drush commands including `cohesion:import` `cohesion:rebuild` and `sync:import`

#### Are there any risks I should be aware of?

Failing to upgrade could result in your deployments or CI failing.

## 6.0.3

### Fix warnings on style guide manager preview

#### What is is it?

Resolved an issue where PHP warnings were thrown when changing and previewing style guide manager values

#### What impact will there be?

Removes all warnings when previewing style guide manager values

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

None.


### Improve caching of endpoint when using a component with existing selects

#### What is is it?

Resolved an issue where multiple requests to the `/select-options` endpoint were not correctly cached when creating content entities. This led to the endpoint being hit an excessive number of times.

#### What impact will there be?

In-browser performance and server load will be improved when placing and editing components on content entities.

#### What actions do I need to take?

A Cohesion import via the UI or `drush cohesion:import` is required on existing websites.

#### Are there any risks I should be aware of?

None.


## 6.0.2

### Improve Dropzone UI on layout canvas to provide more room and a simpler appearance.

#### What is is it?

Resolved issue with drop-zones on layout canvas which was preventing them displaying as columns. This fix also includes improvements to the drop-zone layout, making them take up less space so that the layout canvas is easier to use for content editors.

#### What actions do I need to take?

A Cohesion import via the UI or `drush cohesion:import` is required on existing websites.

#### Are there any risks I should be aware of?

None.

### Generic styles not showing in the WYSIWYG

#### What is is it?

Fixes an issue when using Generic custom styles in the WYSIWYG not showing the correct label.

#### What actions do I need to take?

Clear Drupal caches

#### Are there any risks I should be aware of?

No.

### Cohesion Sync package doesn't find file entity

#### What is it?

In some rare cases, a file entity's ID may contain a space at the start. When importing a Cohesion Sync package containing this file, no changes were detected as the file could not be found.

#### What impact will there be?

If a file entity has a space in the ID, it will remove the space and find the correct file.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.0.1

### Cohesion no longer switches the site into and out of maintenance mode when rebuilding via drush

#### What is it?

In certain circumstances, users were seeing the error "MySQL has gone away" after running `drush cohesion:rebuild`

This was caused by a combination of MySQL configuration and/or a long running CRON or drush command process that running `cohesion:rebuild` command contributed to.

More information: https://www.drupal.org/node/259580

It was decided that developers working with Cohesion should be responsible for bringing the site into and out of maintenance mode if required and that Cohesion should not enforce this.

#### What impact will there be?

When running `drush cohesion:rebuild` note that your site will no longer go into and out of maintenance mode when running this command.

#### What actions do I need to take?

If you require this functionality, you will need to implement it yourself inside your build scripts.

More information: https://www.drupal.org/docs/user_guide/en/extend-maintenance.html

#### Are there any risks I should be aware of?

See above.

### Fixed an issue where icon and font files were not showing

#### What is it?

In certain configurations of Acquia Cloud and Acquia Cloud Site Factory hosting, it was possible that font and icon files were not being referenced on the front end correctly. This resulted in missing font and icons on the front end of the website.

#### What impact will there be?

Users that reported this issue will see it has been resolved.

#### What actions do I need to take?

A Cohesion import and rebuild is required after upgrading to the latest version of the module.

#### Are there any risks I should be aware of?

No

### Initial page rendering performance improvements

#### What is it?

We've improved the time to build Cohesion layout canvas enabled pages for the first paint (before the cache has been built).

#### What impact will there be?

Users making changes to Cohesion powered content entities will see their page rendering more quickly.

#### What actions do I need to take?

No actions are required.

#### Are there any risks I should be aware of?

No

## 6.0.0

### Rebrand from Cohesion DX8 to Acquia Cohesion

#### What is it?

As of version 6.0.0, Cohesion DX8 has rebranded to Acquia Cohesion. Note that the main menu item in the Drupal admin menu is now “Acquia Cohesion” and all references to “DX8" in the user interface have changed to “Acquia Cohesion” or “Cohesion”.

#### What impact will there be?
This is a UX/content change only so there is no functional impact however users will see interface changes

#### What actions do I need to take?

No actions are required.

#### Are there any risks I should be aware of?

No

### Import and Rebuild drush commands are now prefixed with "cohesion" instead of "dx8"

#### What is it?

As part of the rebrand to "Acquia Cohesion", the existing drush commands have also been renamed.

`drush dx8:import` becomes `drush cohesion:import`

`drush dx8:rebuild` becomes `drush cohesion:rebuild`

#### What impact will there be?

Note that the `dx8:import` and `dx8:rebuild` commands are still in place, so your existing deployments will continue to work with no changes.

However, you should plan to update your usage of these commands as they will be deprecated and removed in a future version.

#### What actions do I need to take?

Change your drush commands to the new ones asap.

#### Are there any risks I should be aware of?

No

### Component/Style guide builder - tab fields are now responsive

#### Impact

When you add a `Tab container` and at least one child `Tab item` to your component or style guide forms, these will collapse into a dropdown menu with an ellipsis button.

This is now consistent with how Drupal renders tabs responsively.

#### Conditions

- The screen width is less than `768px` when completing a component form.
- A style guide form is being completed in `Appearance settings` and the preview is active (no breakpoint condition).
- A style guide form is being completed in `Appearance settings`, the preview is hidden and screen width is less than `768px`.

#### Features

- The selected tab always appears at the top of the dropdown, next to the ellipsis button that opens the menu.
- To improve keyboard navigation, focus is returned to the ellipsis button when a new tab is selected.

#### Action required

None - this behaviour happens automatically and is not configurable.

#### Risks

There are no backwards compatibility or upgrade requirements.

### Optional background image on component renders invalid css

#### What is it?

Fixed a bug where setting a image field to a background image on a lower breakpoint would render invalid CSS if this image was not populated instead of inheriting for the higher breakpoint.

### Modal element - jQuery animation settings

#### What is it?

Fixes a bug where a scale animation was being applied to a triggered modal that was set to have no animation (default setting).

Now the modal will instantly appear when triggered, if `jQuery animation` is set to `None`.

### Machine name are now locked after saving

#### What is it?

Machine name of Cohesion entities will now be not editable after you save even if they are not in use.

#### What impact will there be?

the `dx8:import` and `dx8:rebuild` commands are still in place, so your existing deployments will continue to work with no changes. However, you should plan to update your usages of these commands as they will be deprecated and removed in a future version.

### Prevent and warn when importing component or style guide with broken linkage to populated content

#### What is it?

If you import a package through the UI that contains a component and/or a style guide with missing fields that previously had content populated against it, you will see a system warning.

You can choose to override, in which case you would eventually lose the content, or keep the existing entity.

On `drush sync:import` (when using the option `--overwrite-all`) if you import a package with missing fields that previously had content populated against it, you will be prevented from importing and be given a summary of affected entities.

You can choose to ignore it by using the `--force` option, which will ignore the validation and overwrite all entities.

#### What impact will there be?

Reduces the opportunity to accidentally overwrite existing fields containing content. This prevents the loss of content on sites using a distributed design system and Cohesion style guide manager.

### Style guide manager real-time preview

#### What is it?

The Style guide manager now includes a real-time preview. Users can browse around the website, moving from page to page to see how their changes will affect the appearance of the site before applying them.

This new feature makes it much quicker and easier to apply styling changes globally to the theme of your website.

If a style guide field cannot show a preview because it is changing something other than a CSS style, an indicator displays next to the form field telling the user.

#### What impact will there be?

Teams using SGM can now see the changes being in realtime. This allows for faster, more accurate customisation of sites across large multi-site estates for non technical users.

### Improvements to API scalability, fault tolerance and error reporting.

#### What is it?

The Acquia Cohesion API has been significantly refactored for this release to fix some legacy issues related to deploying and rebuilding sites at scale.

Network fault tolerance is improved, and error reporting back to the client is also now more descriptive and useful.

#### What impact will there be?

Less API errors, better reporting capabilities and faster rebuilding when required

### Bugfix: If no cohesion enabled theme is installed, it returns a js error on component preview

This fixes a javascript error when no theme with cohesion enabled is installed and trying to preview a component.

### Define the temporary:// stream wrapper for cohesion to use

#### What is it?

You can set the stream wrapper of the temporary folder for cohesion to use by setting `$settings['coh_temporary_stream_wrapper'] = 'mytemp://';` in you settings.php file.

#### What impact will there be?

More flexibility for developers and dev ops to control the configuration to meet the needs of their business

### Component/style guide manager fields - tooltip support

#### What is it?

You can now add tooltips to your component and style guide manager fields.

When a field has tooltip text specified, a blue help icon will appear next to the label. Hovering over this icon or the label will display the tooltip.

Placement options `Top`, `Right` and `Bottom` are available.

Note:

- The `Entity browser` and `Entity reference` fields have multiple tooltip options as they have multiple fields.
- The `Google map marker` field has a single tooltip option which is applied to the `Address` field in standard view and the `Latitude, longitude` field when in variable mode.

#### What impact will there be?

Tooltips are available in more places improving UX.

#### What actions do I need to take?

If required these can be added retrospectively

### Component/helper category improvements

#### What is it?

- Fixed a bug where `Category 3` and `Category 5` had the same color. Category 3 now has a different color.
- Added three new categories for a total of `15`
- Improved accessibility of category color selector (visual focus state plus `fieldset` and `legend` HTML)
- Colors now have a tooltip on hover, similar to the color palette
- While most categories have similar colors as before, some have been changed to provide better contrast and a more consistent color story. When used effectively, this can be used to create sub-categories within a specific color group.

#### What impact will there be?

Significant improvements in accessibility. End users will see a different color when first using the updated version.

### Region element has a new option for all themes

#### What is it?

The region element has a new option, "All themes". When selected, all regions in the active themes are shown in the region select list and will render out the region, regardless of what theme is set as default.

#### What impact will there be?

More flexibility in the use of regions in components and templates for sites that use multiple themes and Style Guide Manager.

### Fixed issues around required fields and tokens

#### What is it?

- Fixed a bug where it was possible to circumvent form validation and apply the sidebar editor when in variable Mode.
- Fixed an issue where adding a token to a required field did not count as the field having a value for its validation criteria.

### Additional warnings when deleting a component that is in-use.

#### What is it?

When attempting to delete a component that is in-use, the site builder will now see a list of entities where the component is in-use on the delete confirmation page.

If the component is in-use on component content and the site builder deletes it, the component content entities for the component will also be deleted.

#### What impact will there be?

It will be easier to detect potential data loss issues with package imports.

## 5.7.11

### Multiple contexts on single element not applying correctly

Fixed an issue if multiple contexts are set on an element and the pass condition "Only one criteria must pass" was set, the context wasn't showing/hiding the element as expected.

### Styles missing when aggregation turned on

Fixed an issue where in some cases having aggregation turned on would result in some missing styles.

## 5.7.10

### Colors in the color palette could not be selected in certain circumstances

Fixed an issue where if you had 2 or more colors only differentiated by their alpha transparency value then clicking any of them in the colour picker would always just select the first one.

## 5.7.9

### Style guide removed if all values are inheriting from parent theme

If all values of a style guide are set to inherit from the parent (ie: not edited), then the underlying entity will be removed.

Therefore if you do a full export this entity will no longer exists. This prevents overriding a child site style guide values when doing a full export/import.

### View template not rendering correctly when adding component with dropzone

Fixes a bug where adding a component with a dropzone on a view template would break the styles and markup of the view.

### Link element - Open modal type - Trigger ID renders as link text

Fixed an issue where using a link element to trigger a modal was rendering the `Trigger ID` as the link text instead of its `ID` value.

### Reduce the amount of temporary component preview

Improvement has been made to reduce the amount of records saved in the temporary key value storage when previewing a component which was resulting in a large database.

### Component field connected to font weight

Fixes an issue where using a component field with font weight causes incorrect values to be returned.

### Field tokens are not copied when duplicating a parent component

Fixes a bug where duplicating a component with a component inside would not retain its field linkage.

### Style Guide Manager - "0" as value not working

Fixes an issue that prevented using `0` as a value for a style guide field.

## 5.7.8

### PHP out of memory on drush dx8:rebuild

Improves memory usage when executing `drush dx8:rebuild` so that less memory is used to run the process.

## 5.7.7

### Icon picker not rendering icons

Fixed a bug where uploaded icon libraries would render correctly on the front end but not in the icon picker.

### Style guide values lost

Fixed a bug where some style guide values were lost after saving.

### Custom style ordering

Fixes a bug where ordering custom styles with similar weights were not retained.

### Drupal 8.8.0 compatibility

Fixed a few `dblog` warnings and code style issues related to Drupal `8.8.0`.

## 5.7.6

### Nested components not rendering with multiple Cohesion enabled themes enabled

Fixed an edge case where components that were placed inside dropzones that were themselves inside other components were not rendering.

### Improvements to Cohesion sync error reporting

When importing an entity with a UUID / machine name mismatch, the errors provided to the end user were not helpful. This has been fixed and provides enough information for the user to start debugging the issue with their package.

Example:
```
The validation failed with the following message: Custom style with UUID 00000000-0000-0000-0000-0000000000000 already exists but the machine name "coh_existing_machine_name" of the existing entity does not match the machine name "coh_mismatched_machine_name" of the entity being imported.`
```

## 5.7.5

### Improved warning message for missing template files.

When a generated component twig template file was missing from the file system, Cohesion was printing "something here" on the front end of the website.

This message was not helpful and has been replaced with a message indicating the missing template suggestion.

### Style guide form - padding missing

Fixed an issue where there was some padding missing from `Group accordion` fields when used in a style guide form.

This only affects the form shown in Drupal `Appearance settings`.

### Existing theme settings not updating when saving a style guide

Fixed an issue where theme settings that are not included in a style guide form (such as logo and favicon) were not being saved when saving theme settings performed a rebuild of Cohesion entities.

### The numerical value of the range slider not always displayed correctly

Fixed an issue where the numerical value of the range slider was not always rendering correctly.

## 5.7.4

**Please note, upgrading to this release will require a re-import and rebuild which can be performed with `drush dx8:import` and `drush dx8:rebuild`**

### Bugfix: correctly look up available text formats by account

Fixed an issue where available text formats were being queried by the current user role(s) instead of directly with the user account object.

This meant that if user 1 had no role, they were unable to access any text formats.

### Range slider - thumb positioning

Fixes an issue where the range slider thumb was sometimes positioned incorrectly when loading a page.

### Base and custom styles - pseudo content image

Fixed an issue that prevented rendering of `:before` and `:after` pseudo element images that were specified in base and custom styles.

Content image styles applied directly to elements are unaffected.

You can now also select a `Drupal image style`.

**A rebuild is required to update all existing styles, otherwise re-save base/custom styles on a case-by-case basis.**

### Bugfix: Cohesion layout canvas preview

Fixed an issue where the Cohesion layout canvas field was not rendering correctly on content entity preview pages.

### Component - existing select field

Fixed an issue when tokenizing an existing select field in a component.

This was causing the following validation error when selecting a value on a component instance:
```
Invalid type, expected ["string","number","boolean"]
```

### Fixed an issue where inserting media into the WYSIWYG element could fail to save

Entering an entity via the entity browser and pressing apply without ever focusing into the WYSIWYG could cause the embedded entity data not to be saved and you would end up with an empty WYSIWYG when re-opening.

### Fixed an issue that prevented the order of custom styles changing when they were re-ordered in the UI

This fixes an issue that prevented the re-ordering of the custom styles.

### Fixed an issue with the help text element on style guide forms

Added code to ensure the text in the Help Text element is loaded on the SGM forms.

## 5.7.3

### Fix an issue where the master template was not rendering

This fixes an issue where when using style guide manager tokens in master template it would not render the master template.

## 5.7.2

### Fixed issue saving master templates with multiple enabled Cohesion themes.

This fixes templates failing in some edge cases and the following `dblog` warnings:

```
Warning: Illegal string offset 'template' in Drupal\cohesion\Plugin\Api\TemplatesApi->send()

Warning: Illegal string offset 'themeName' in Drupal\cohesion\Plugin\Api\TemplatesApi->send()
```

## 5.7.1

### Fixed function declaration warning.

Fixed a function declaration that was incompatible with the interface. It was causing this warning:

```
Declaration of Drupal\cohesion\StreamWrapper\CohesionStream::basePath($site_path = NULL) should be compatible with Drupal\Core\StreamWrapper\PublicStream::basePath(?SplString $site_path = NULL)
```

## 5.7.0

### Style guide manager

The style guide manager is a new (optional) sub module and will need to be enabled to use it (`Cohesion style guide manager` via the UI or `cohesion_style_guide` via drush).

You can use the style guide manager to create theme-specific overrides for your website's styles and appearance settings.

Theme specific overrides can use theme inheritance. This means a sub-theme will automatically inherit the settings of its parent theme.

Changes made to sub-theme settings will override its parent theme settings. The style guide manager has two main interfaces:

1. Style guide builder - this is an interface for defining theme-specific overrides. The output of the style guide builder is a `Style guide`. This can be accessed at: `/admin/cohesion/style_guides`.

2. Style guides - this is an interface for applying values to your theme-specific overrides. These overrides are theme specific and can be access on the appearance settings form for any Cohesion enabled theme.

The style guide definition entities (1 above) and style guide manager instance entities (2 above) are config entities that work with the Cohesion sync module.

For more information on how to set up and use the style guide manager feature, please refer to the latest Cohesion user guide.

### Bugfix: Video in Modal continues to play

Fixes issues when there is a Video in a Modal and the modal is closed the video continued to play.

Fixed for native HTML5 videos, YouTube and Vimeo.

### Bugfix: Rendering regions of inactive themes

Fixed a bug where regions for inactive themes were being rendered if they had the name machine name as a region in the inactive theme.

For example, adding two `content` regions from different themes would render the `content` region of the active theme twice.

This is now fixed as the system checks if a region belongs to the active theme before rendering it.

### Bugfix: WYSIWYG form fields in custom elements not working

Fixed and issue where tokenizing a custom element WYSIWYG in a component didn't render the WYSIWYG content.

### Lock and unlock the Font stacks

Adds the ability to lock and unlock the Label and Variable fields when adding a Font stack.

### Bugfix: Authentication via settings.php and drush

Fixed an issue where defining the API authentication credentials in settings.php was not working with Cohesion drush commands.

### Toggle parent menu visibility

Added an interaction option to the menu button element to allow site builders to add a `Menu button`, which will toggle the visibility of the parent menu.

### Font picker field added for use on Style guides

Added a new field type that allows for the selection of fonts. The list dynamically updates and will pull newly added/removed fonts.

**Note: this field is only available for use with style guide entities.**

### Bugfix: Fixed API warning

A Drupal warning was being thrown when saving custom styles that had ben upgraded from an earlier version of Cohesion and had an unset image background. This is now fixed.

### Bugfix: empty styles edge case

Fixed an edge case where a malformed response from the API could fail to update the website stylesheet correctly.

### Support for tokens in View item element settings

When building a view template, it is now possible to toggle variable mode in the View item settings form and apply tokens to the view mode settings.

### Component and helper category permission changes

Users with permissions to create, edit and delete component and helper entities will:

- Automatically have permissions to select from any category on the component and helper entity form
- Be able to select from any component or helper in the Cohesion sidebar.

### Element forms UI changes.

The "Toggle variable mode" and "Open token browser" buttons in the element ellipsis menu have been moved to the toolbar containing the title and properties menu.

### Bugfix: Using images and gradients together on an element inline style.

When using a background image and a gradient together on an inline style for an element, the gradient was not being rendered in the CSS. This is now fixed.

### Bugfix: Conflicts with remote stream wrappers

Resolved an issue where Cohesion would conflict with stream wrappers that did not invoke `getDirectoryPath` (remote
stream wrappers for example).

### Support for embed media plugins in Cohesion WYSIWYG

Added support for CKEditor plugins like Drupal 8.8.x "Insert from Media Library" and "Node" that use the "Embed media" or "Display embedded entities" setting in the text format definition.

These plugins can now be used in Cohesion WYSIWYG elements and Cohesion WYSIWYG component form elements.

### Color palette - tagging

Adds the ability to tag colors in `DX8 > Website settings > Color palette`.

Website builders can then group certain colors and then restrict a color picker component field to specific tags.

### Bugfix: Package upload button disabled state

Fixed a bug where the upload button on the sync package upload form `DX8 > Sync packages > Import packages` was always enabled.

This meant it was possible to upload the validation of a package before it was complete by clicking the button prematurely.

This button is now disabled until the validation is complete.

### Bugfix: Saving an element as a helper and then placing the helper on the same layout canvas as the original element.

Fixed an issue where after creating a helper from an element, it would have the same UUID as the original element.

This would clash if you placed that helper back onto the same layout canvas it was saved from, resulting in form data being overwritten with blank values.

## 5.6.2

### Bugfix: Elements inside dropzones being lost when importing templates and components.

Fixed an issue where a element inside a dropzone was being removed from a template or component layout canvas when importing an entity that was new to the local site.

### Bugfix: Helpers not showing in the sidebar browser

Fixed a bug where helpers containing components with drop zones were not showing in the list of helper in the sidebar browser.

### Bugfix: Video controls assets not showing

Fixed a bug where the video controls assets were not loading correctly from the right path.

### Setting Cohesion API and organization keys in settings.php

Fixed a bug where Cohesion configuration settings could not be set in environment settings files. See `README.md` for more details.

### Bugfix: XSS validation applying to component fields

Fixed an issue where the XSS validation was sometimes being applied to component form field data.

This is now fixed so XSS validation only applied to elements settings.

### Bugfix: Canvas preview

Fixed an issue that prevented the canvas preview working when opened in a new window.

### Enabling RESTful on update

The `RESTful Web Services` module is now enabled as part of an update script.

This only affects websites being upgraded from versions prior to `5.6.0` and the rest module does not need to be enabled manually before upgrading.

## 5.6.1

### Bugfix: composer issue

Removed `Entity reference revisions` patch from Cohesion `composer.json` as version `1.7` of `Entity reference revisions` now includes the patch.

### Custom element fields can now be required

When developing custom elements for Cohesion, developers can now make text inputs, text areas, selects and file browsers required and set a custom validation message.

## 5.6.0

### SCSS variables behave in a more predictable way

- `$coh-color-` variables from the color palette can be used within SCSS variables.
- SCSS variables can be used within the value field of other SCSS variables. Example: `calc ($var1 + 10px);`.
- The API now catches syntax errors in SCSS variable values and prints warnings within the generated CSS, making debugging easier.

### Webform usage plugin

If a Cohesion Custom style is used on a Webform entity, the style will now show as in-use by the Webform.

### Block element now available within menu templates

The `Block` element can now be placed within a Cohesion `Menu template`.

### Set the default cohesion sidebar list view

There is a new settings in the global cohesion settings page: `/admin/cohesion/configuration/system-settings` called `Default sidebar view style`.

This allows the site administrator to set the default list view style of cohesion elements, components and helpers in the sidebar. Thumbnails are show by default.

As before, this setting can be changed by the user by clicking the toggle icon at the top of the sidebar and those changes persist across the browser session.

(Existing sites being upgraded will see the original list by default unless the user has already changed this for their session). 

### XSS validation in element forms

Cohesion now validates all element settings inputs for script tags and other potentially dangerous markup using the Drupal core `Xss::filterAdmin` utility.

Examples of potentially dangerous markup that are filtered:

- `<script>` tags within markup prefix and suffix fields.
- `<object>` tags within markup prefix and suffix fields.
- `onClick` and other Javascript event attributes.
- Custom `href` attributes that contain values prefixed with: `javascript:`

There is a new permission that can be applied to certain roles to allow users to bypass this check: `Bypass XSS validation in element forms`.

This permission has the `restrict access` flag so will appear in the permissions table with the label `Warning: Give to trusted roles only; this permission has security implications`.

Notes:

- If you're upgrading an existing site, it's important that you review your role permissions after the upgrade and only give this permission to users that absolutely need it (the `user=1` administrator will have this permission by default).
- Bypassing this check to add javascript libraries or snippets in elements is not recommended. Javascript libraries should be added to source control within your theme or a custom module and attached programmatically via the Drupal core library system: `https://www.drupal.org/docs/8/api/javascript-api/add-javascript-to-your-theme-or-module`.

### ‘Elements’ will be disabled by default and not show in the sidebar browser when using the ‘Layout canvas’ field on a content entity.

Using primitive elements to create content means there is no separation between content and design. This approach is discouraged in favor of using `Components` which have a clear separation between content and design.

To discourage page creators from using primitive elements for content, they will now be disabled by default and not show in the sidebar browser.

When using the `Layout canvas` on content entities, components will be shown first for all users.

Site builders can choose to enable primitive elements on the layout canvas field within the field settings (although this is not recommended).

Cohesion `Helpers` will still be available in the sidebar browser unless they include primitive elements. In which case, they will be hidden.

Existing instances of the layout canvas field on sites upgraded to this version will remain unaffected and can continue to use primitive elements on the layout canvas (although this is not recommended).

### Bugfix: Using global tokens in templates

Fixed an issue where global tokens like `[current-page:title]` was breaking the generated twig.

### Bugfix - Tokenizing values in styles on elements when building a component

Fixes a bug when tokenizing values on element styles when switching between levels in the style tree.

### Bugfix - Using images containing ampersands in the filename resulted in a server error

Fixed a bug where selecting an uploaded image where the filename contained an ampersand resulted in a server error.

### New dependency on the core RESTful Web Services module.

Cohesion is now using the `RESTful Web Services` core module for its endpoints. Before upgrading you MUST enable this module.
‌
### Modal trigger elements - `Trigger ID` field

Focus will return to the body when a modal is closed. If you would like focus to return to your trigger element, give it a unique ID.

To make this easier, we've added a `Trigger ID` field to elements that support the `Open modal` interaction type. These are currently:

- Link
- Button
- Container
- Column
- Slide

You can still add an ID through `Markup > Properties > Classes and ID` as with other elements, but this value will override the `Trigger ID` value.

### New component form field - Range slider

The range slider field can be used as an alternate input method for number-based fields in your components.

You can specify `min`, `max` and `step` values, as well as a default value within the specified range.

### Enable Cohesion on a theme

When creating a new theme that is Cohesion enabled, you will need to add `cohesion: true` to the .info.yml of your theme.

**Note: if you extend from the base `cohesion_theme` you do not need to add this flag because the system will detect the flag has been set in the parent theme.**

If you have an existing theme extended from `cohesion_theme`, you do not need to add this flag. Your existing theme will just work without modification.

Because of this change, the selector for the global theme on the System settings configuration page `/admin/cohesion/configuration/system-settings` has been removed.

### "Existing selects" used in components are now dynamic, not hard coded on save.

Selects chosen from the `Existing select` picker on a component will now dynamically load options when used in components.

Previously the options were fetched when the component was created and not updated afterwards.

### Removed experimental layout builder module.

The removes the ability for site builders to see the content templates injected around the layout builder canvas at `node/x/layout`

Other layout builder support is unaffected, including:

- Custom block templates can still be themed with Cohesion (`/admin/cohesion/templates/content_templates/block_content`)
- Tokens can still be used in custom blocks.
- New "Drupal -> Content" element still available in the Cohesion sidebar browser.

**Note: if `cohesion_layout_builder` is enabled on your site, you should uninstall that module before upgrading to this version.**

## 5.5.6

### Using Drupal tokens in slide container, slides to show and scroll does not process the Drupal token

Fixed an issue where using Drupal tokens within Slides to show and slides to scroll did not process the Drupal token.

## 5.5.5

### Editing other fields in sidebar after setting a link to page using typeahead could incorrectly set the link value

Fixed an issue where on load a Typeahead *model* value could be incorrectly set to the *view* (label) value.

### Duplicated elements don't display variable mode correctly

Fixed a bug where after being duplicated the fields in an element that have variables in are not correctly displayed as yellow and show the token/variable instead of the preview text.

## 5.5.4

### Moderated translation not returning the correct layout canvas data

Fixed a bug where editing the translation of a node in draft would return the canvas of the published version instead of the draft content.

### Content images on Pseudo element rendering public:// instead of path

Fixed a bug where an image added to a content pseudo style would not convert the public:// path to it's internal path.

### Module compatibility with web profiler

Fixed a bug when the web profiler module was installed on your Cohesion website, which was causing error messages.

## 5.5.3

### External urls on background images looking at current domain

Fixed bug where external urls to background images would see the domain stripped out.

### Menu button elements created before 5.5 can error in certain circumstances

Fixed an issue where menu button element could throw an error when clicked after being upgraded from an older version of Cohesion.

## 5.5.2

### Component and helpers category not selectable after creation on restricted permissions

If a user as admin permission on component or helper category you don't have to enable the permission on each individual ones for the user to be able to select one.

### Font libraries not uploading to the correct folder

Fix a bug where font libraries were not moved from the temporary directory to the cohesion directory therefore not loading when included in the head.

### Cohesion sync packages not accessible unless site admin

Fixed an issue where Cohesion sync packages were only accessible to users with the role of Administrator.

Roles that have the `Access Cohesion Sync` permission will be able to manage sync packages.

### Drupal config import failing on Cohesion entities

Fixes a bug where Cohesion was creating content template config entities on config import of view modes and entity type, which caused the imported content templates to fail.

## 5.5.1

### Background image enabled but not set a top breakpoint

Fix a warning that was thrown ( in `Drupal\cohesion\LayoutCanvas\ElementModel:122`) if you had an element with background image enabled at the top breakpoint bu had not selected any image.

### WYSIWYG element - first line of pasted text cut off

In rare cases, pasting a large portion of text into a WYSIWYG element resulted in the first line of the content being cut off and unscrollable. This has now been fixed.

### Link to page - typeahead search improvements

Fixed an issue where the `Link to page` component field's typeahead search functionality was behaving incorrectly when typing. Characters were being removed when new results were returned making it difficult to use.

## 5.5.0

### New element - Modal

Within the `Interactive elements` section of the sidebar browser, you can now add a `Modal` element to your layout canvas.

This will allow you to display content in an accessible popup dialog and can be triggered by the following elements:

- `Link`
- `Button`
- `Container`*
- `Column`*
- `Slide`*

*_`Link and interaction` settings need to be enabled through `Properties > Settings`._

To link a trigger to a modal, you need to select `Modal` from the interaction `Type` dropdown and specify the `Modal ID` of the modal to open.

You can also trigger a modal through a `WYSIWYG` link. For optimal accessibility, this link should point to an alternate location where the modal content can be viewed (should JavaScript be disabled).

To connect this with your modal:

1. Create your link in the WYSIWYG as per your preferred method.
2. Toggle into `Source` mode.
3. Add `data-modal-open="modal-id"` as an attribute to your link, where `modal-id` is the id you've given to your modal.
4. Toggle out of `Source` mode and save your changes.

**You may need to add `data-modal-open` as an allowed HTML attribute to your text format settings **for this to work.****

The modal element has several settings that you can configure:

- Dialog ID, animation, position, custom style and auto open/close
- Close button visibility, text, custom style and position
- Overlay visibility, click to close and custom style
- Generic layout style to be applied on outer container

For optimal accessibility, when the overlay is visible focus is trapped in the modal using the [inert polyfill from Google](https://github.com/GoogleChrome/inert-polyfill).

### Fix revisions not being created on non moderated entities

There is now a patch on the `Entity reference revisions` module on Drupal.org: `https://www.drupal.org/project/entity_reference_revisions/issues/3025709`.

This fixes revisions not being created on non moderated entities and we have added it to our `composer.json` file.

You will need to enable patching according to `https://github.com/cweagans/composer-patches#allowing-patches-to-be-applied-from-dependencies`.

### New element - Read more

A new interactive element called `Read more` is now available to use on the Layout canvas. This allows site builders to show/hide content on click of a button.

The initial expanded/collapsed state of the content can be set per breakpoint, as well as the corresponding button text in each state.

### Use component fields on column width - push - pull and offset

You can now attach a component field to the column width - push - pull and offset fields

### New element - Menu button

In menu templates, you can now add a new element called `Menu button`. This allows you to toggle submenu visibility when you want the sibling `Menu link` to click through to a page.

It has the same click animation settings as the `Menu link` element.

### Lock entities to prevent them being updated by Sync.

It's now possible to decouple / lock a Cohesion entity on a site. This means that this entity will be ignored by Sync when running an import.

For example, if you have a `package.yml` that contains a component and you import that component to your site. Now you lock the component and make some changes.

If you attempt to re-import the same `package.yml` file, Sync will ignore the locked component and report that there are no changes to apply.

An example use case: This feature could be used to make local changes to an entity that is contained inside an external design system that automatically applied to the site on module update or via a CRON process.

To lock/unlock an entity, visit the entity list builder page and under the action menu on the right hand side there will be the option `Lock` or `Unlock`.

### Support for Chosen module

Previously it was not possible to use the `Chosen` module: `https://www.drupal.org/project/chosen` with the layout canvas on the same content entity form. This has now been fixed.

### Entity browser element

You can now add a new element called Entity browser. This element allows you to browse entities using an entity browser and display an entity in a specific view mode.

You also have a component field element that you can attach to this element to give this capability to site editors.

A new dependency on the `Entity browser` module `https://www.drupal.org/project/entity_browser`.

You will need to install and enable this module if upgrading from a previous version.

### Slider container - slide count

You can now add a slide count to your slider container. This is in the format of `current slide / total slides` and is best used when `Slides to show` is blank or set to `1`.

It has the same positioning options as slider pagination, has a helper class of `coh-slide-count` and you can apply a `layout` or `generic` custom style to it.

To enable these options on your slider container form, navigate to `Properties > Navigation > Slide count visibility` and `Properties > Navigation > Slide count style`.

### Link element - layout canvas child support

You can now add child elements to the `Link` element - by default, it will appear on the layout canvas as collapsed.

**_To update existing link elements, you will need to do a rebuild._**

### Link autocomplete field no longer limited to nodes

Previously, the "Link to page" field on the link element and the link component form field only allowed users to search for links to nodes.

This has been extended to allow users to link to views pages.

### Analytics data layer

The analytics tab on elements now has options for adding data layer key and value pairs.

This data can be pushed to Google tag manager when triggered by a selected event.

### New options for deploying Sync packages

- There is a new option to specify a path to a local or remote file when deploying a package using `drush sync:import`.

- Module developers can include a list of `*.package.yml` files that will automatically be installed when their module is enabled.

For information about these new features, see: `modules/cohesion_sync/README.md`.

### Helpers can now be restricted by content entity bundle

Previously, it was ony possible to restrict certain components for use on certain content entity bundles. Helpers were available everywhere.

The same settings that exist for this on the component form have been copied to the helper form so site builders can now restrict helpers for use by content entity bundle.

### Image lazy loading

`Image` and `Picture` elements now have a `Lazy load` option which can be set to make them load only when a user scrolls them into view, deferring load until they are needed.

### Commercial Font License Information

When uploading fonts it is now possible to enter license information which will be displayed as a comment in your generated CSS file.

### Sync packages and updates to existing sync functionality

#### New package entity

The sync module menu items have moved from under `Config > Development > Sync` to `DX8 > Sync`.

To export packages via the UI, you need to create a new package entity at: `/admin/cohesion/sync/packages` and define your package contents.

On the package list page, there will be a new button "Export package as file" to export the package definition and all dependencies that you defined.

There is a new permission for administering the new "package" entities. This is under: "DX8 Sync packages -> Grant access to edit and manage DX8 sync packages."

#### Change to full export behavior

When defining which entity types to exclude from a full export in full export settings (`/admin/cohesion/sync/export_settings`), DX8 Sync will now exclude all entities of those types regardless of their dependencies.

#### Importing custom styles

Fixed a small issue where DX8 Sync always detected custom style entities as changed even if they were identical.

#### Uploading large files via the UI

When uploading package files via the UI at `/admin/cohesion/sync/import`, it's now possible to upload very large files that exceed the PHP `upload_max_filesize` limit.

### Inline element

A new content element called "Inline element" is now available to use on the Layout canvas.

This allows site builders to add an HTML inline element such as subscript from a pre-defined list or use a custom one.

### New system setting to restrict DX8 to a specific theme

In `DX8 > Configuration > System settings configuration` there is a new setting called `DX8 enabled theme`. This restricts DX8 templates and styles from only applying to a specific theme (for use with modules like AMP).

For existing sites, this setting will be set to `All themes` and work as normal.

### Bugfix: Width of content in style preview sometimes exceeded width of preview on slower connections.

Fixed a race condition that meant the width of the content inside the style preview was not set to be constrained by the width of the WYSIWYG.

This meant right-aligned elements could be displayed off the right edge of the preview and therefore appear invisible.

### Bugfix: context pass condition field not tokenizable

Fixed an issue where it wasn't possible to tokenize or map a component field to the context pass condition field. There was no tag or warning on this field to indicate this limitation.

The field can now be tokenized or mapped to a component form text or select field.

### Drupal image style on background images

You can now apply drupal image style to background images in the style builder on styles and elements.

### Layout canvas validation improvements

Certain container elements no longer blindly accept child elements. The list of allowed child/parent pairings are as follows.

```
row-for-columns -> column
google-map -> google-map-marker
accordion-tabs-container -> accordion-tabs-item
slider-container -> slide
menu-list-container -> menu-list-item
list-container -> list-item
form-tab-container -> form-tab-item
```

In addition a number of elements have been designated "universal" which means they can be placed inside any container, regardless of the above.

This includes custom elements and all drupal elements such as drupal blocks, views etc.

### Website settings - Color palette

Adds the ability to link and unlink a color label from the variable name when the color is not in use.

### Component and helper categories

Instead of the previously fixed list, any number of new component and helper categories can be defined by the site builder.

Site builders can access the categories listing pages and create new categories via the menu at `DX8 > Components > Categories` and `DX8 > Helpers > Categories`.

**Note: existing sites will have the previously hardcoded entities converted to editable categories and all references inside components and helpers will link to these new categories.**

- A core permission will be added for each category to: `People > Permissions`. If you have configured these permissions on an existing site, they will need to be configured again for these new permissions.

- New installs of DX8 will come with a set of pre-defined categories that can be edited or removed by the site builder.

- Categories can be re-ordered on their list builder pages and this order will reflect in the sidebar browser when selecting components.

- The category and helper entities within individual components can also be re-ordered on their list page.

### `Video` and `Video URL` component field elements

When you specify a Vimeo or YouTube URL for these elements, the control settings for them will be ignored in favour of their native controls.

This is because they still depend on embedded iframes and can result in double controls being displayed.

#### Exceptions

- **Business/Pro Vimeo account** - native controls can be disabled and HTML5-compliant custom embed URLs can be used so that element control settings can be used.
- **YouTube URL with controls disabled** - this setting will be respected, if present in the URL (`controls=0`).

**For best results a CDN-hosted, HTML5-compliant format (MP4, OGG, WebM, MKV) should be used with these elements.**

### Base styles - Add base styles and edit selector

You can now add new base styles and specify their css selectors. You can also edit the selector of base styles that came from the pre-defined list.

The reset capability has been removed so you can now delete base styles.

### Allow pseudo fields to be used in templates

You can now add pseudo fields in content templates using the field element.

### Picture element - multiple images per breakpoint

You can now add multiple pictures per breakpoint, which is useful for adding multiple image formats.

**Note: the browser will use the first type that it matches, so make sure you specify your image formats in order of preference.**

### Element browser - Drupal block element - styles

You can now add styles to a Drupal `Block` element, either by selecting a `Layout style` in the element settings, or adding properties through the `Styles` tab.

### Style builder - background images/gradients

The background image styles section has been updated to provide more flexibility. Previously, if the background image dropdown option was selected but no image was specified, it would result in `background-image: none` being set in the styles.

#### Improvements:

- New background image dropdown option: `None`
- Background properties are now available on gradients and can be set without an image/gradient being specified
- Background images now inherit across breakpoints

#### Backwards compatibility

Following upgrade and a site rebuild, any breakpoint that had background image selected but no image specified will be converted to the new `None` option.

### Machine name field on DX8 config entity forms.

Previously, the ids for DX8 config entities were generated automatically when saving their forms. There is now a machine name form field on DX8 entity form pages which sets this id.

Note that this will affect the filename of generated `.twig` templates making them easier to manage. All existing entities and generated `.twig` files will be unaffected.

### Canvas preview

You can now preview your layout canvas as you are creating your layout. Clicking `Show preview` will show a live preview as you modify the elements.

- Layout canvas preview is available for Components, Helpers, Content Templates and Master Templates.
- Drupal field, Dropzone placeholder, Drupal field item, Drupal content, and Breadcrumb all show a placeholder (blue/striped box) that is resizable.
- Placeholder size is stored and retains it’s size after page load.
- Other Drupal elements (e.g. blocks) will actually render in the preview.
- You can hover over elements in the preview when the window is popped out and it will highlight those elements in the layout canvas.
- You can hover over elements in the layout canvas when the preview window is popped out and it will highlight those elements in the preview.

#### Features

- Real time preview updates as you edit your component
- Multi screen editing (pop out the preview multiple times to see the preview reload in real time at different browser resolutions)
- Breakpoint selection
- Grid System guide preview display
- Scale to fit for viewing large breakpoints on small screens
- Custom column width preview
- Custom background color preview

### Sidebar browser - components, component content and helpers now have tooltips

Previously when creating components, component content and helpers with long names, it wasn't possible to see the full name in the sidebar browser.

You can now see this when hovering the item in the sidebar browser, in either list or thumbnail view. If you have uploaded a preview image for the component or helper, this will also be visible on hover.

Keyboard focus for the sidebar browser has also been improved, both functionally and visually.

### WYSIWYG form element now fully supports text formats

The WYSIWYG element and component form field now allows to use all text formats, following Drupal standards.

If you have implemented a custom element using a WYSIWYG field you need to change it's default value to an array as follow

```
php
'mywysiwygfield' => [
    'htmlClass' => 'col-xs-12',
    'type' => 'wysiwyg',
    'title' => 'Title of my WYSIWYG field.',
    'defaultValue' => [
        'text' => '<p>This is some default content.</p>',
        'textFormat' => 'cohesion'
    ]
],
```

## 5.4.11

### Bugfix: Master template using default on views with path parameters

Fixed a bug where the selected master template in a view was not rendering and was falling back to the default one if you had a dynamic parameter in your view path on view pages.

Example: `/path_to_view/%id/`

## Animate on view items not appearing on mobile

Fixed a bug when animate on view is disabled for touch devices, the elements set to animate were not always shown.

## 5.4.10

### Bugfix: Tab items with long titles in component form builder losing padding

Fixed a small styling issue where any text in tab items on the component form builder was not correctly formatted if it wrapped to two or more lines.

### Bugfix: Menu is-active class is not added to Home menu links

Fixed a bug where having `<front>` as a url in menus would not add the `is-active` class on front pages.

### Bugfix: It is sometimes not possible to rename selectors in the style tree

Fixed an issue where some items in the style tree were unable to be edited after they were created. These items should now be editable as usual.

### Bugfix: using menu_link_content on menu link config

Fixed a bug where using `menu_link_content` on menu link config would throw a warning.

## 5.4.9

### Bugfix: Tokenizing context on a component

Previously when tokenizing the context field on a component, the front end would throw a Twig runtime error. This is now fixed.

## 5.4.8

### Bugfix: Removing colors in the color palette from the WYSIWYG still show in the 'Inline styles'

Fixed a bug where if a site builder unchecked `Available in WYSIWYG` on a color in the color palette, it would still show in the WYSIWYG `Inline styles`.

## 5.4.7

### Bugfix: Sync attempting to export component preview images that have been removed

Fixed a bug where if a site builder creates a component with a preview image and then uses that component somewhere, the preview image file is tracked as in use even if both the prewview image file entity and file are removed.

Note that element preview images will now be stored locally in public://element-preview-images/ instead of public://cohesion/element-preview-images/

### Bugfix: Hovered menu item behaves erratically if your mouse is over it when the page loads

Fixed a bug where the initial state of hovered menu item can get inverted when mouse leave is fired before mouse enter (e.g. on loading a page with the mouse over the menu item).

**An import via the UI or `drush dx8:import` is required for this change.**

## 5.4.6

### Added in Sync package validation before export

Previously when exporting, packages were streamed directly to files or the browser without testing for export errors.

This meant that any errors with an export would show up in the `package.yml` file as printed error messages, corrupting the Yaml.

The export now streams the export to a temporary file before validating and serving to the user.

Errors with package exports are shown to the user in UI or on screen when using drush.

### Bugfix: Tokenizing accordion tabs container `Start state`

Previously it was not possible to tokenize the `Start state` field in the element. This is now fixed and can be tokenized as expected.

### Bugfix: Website settings admin link text incorrect

When using certain contributed admin themes the admin links on the website settings page were incorrect.

## 5.4.5

### New permission for DX8 Sync

Previously DX8 sync was accessible if the user had access to core config import/export. A new permission has been created called `Access Cohesion sync` which will grant access to the DX8 sync admin interface.

### You can no longer import packages exported from later versions of DX8

DX8 sync only supports importing packages created from the same or older versions of DX8 (there is no way to downgrade a package on import). Importing packages created from newer versions of DX8 resulted in unpredictable behavior and corrupted data.

The importer now tests this and blocks imports created from later versions of DX8. It shows a meaningful error in the UI and drush instead of just crashing.

### Bugfix: Color/Icon picker could appear off the screen

When opening the blade menu with a color/icon picker in it, it was possible for it to load the picker off the top of the screen.

## 5.4.4

### Specify a filename when performing a sync export via drush.

There is a new command line option for setting the filename when performing a DX8 sync:export via Drush.

See: `/modules/cohesion_sync/README.md` for more details.

### Bugfix: default styles not initially set to correct breakpoint when grid is set to mobile first

Fixed a bug where default styles on custom, base and element styles were not initially loading as mobile first when responsive grid settings are set to mobile first.

### Bugfix: DX8 libraries and template not displaying on admin pages for user without permission to see admin theme

Fixed a bug where users without admin theme permission would not see the DX8 theming and templates.

### Bugfix: component value having a token and some text does not render correctly

Fixed an issue where adding a token and some text to a component field (eg. `[node:title]sometext`) was returning the text part as a UUID.

### Alter the list of fields on the Drupal field element by bundle

You can now alter the list of available fields on the Drupal field element by entity type and bundle.

## 5.4.3

### Bugfix: initial DX8 asset import cannot complete on Drupal 8.7-beta

Fixed an issue that made it impossible to set up a new site running DX8 on Drupal core `8.7-beta`.

### Bugfix: validation on responsive grid settings page was failing to update in some cases

It was possible to end up in a situation where the form was valid but would not let you save the page.

## 5.4.2

### Bugfix: default images on components causing Sync import issues

In certain cases, Sync import was failing when an `export.yml` file contained incorrect references to default images on components.

### Bugfix: imported colors missing from color picker

In certain cases, imported colors were missing from the color picker list but were available on the colors website settings page. This is now fixed.

### Bugfix: component context contextual_preprocess Drupal\Core\Template\Attribute warning

Fixed an issue on master template where a `Drupal\Core\Template\Attribute` was passed to `contextual_preprocess()` therefore throwing a notice about `Indirect modification of overloaded element`.

### Bugfix: invalid twig when field element has no field defined.

When adding a Drupal field element to a layout canvas and leaving the field set to `None`, DX8 rendered invalid twig. This is now fixed.

## 5.4.1

### Bugfix: component forms saved as helpers were uneditable

In `5.4.0` we introduce the ability to save component forms as helpers. When editing these helpers via the UI at `/admin/cohesion/helpers/{id}/edit` the layout canvas was unusable. This is now fixed.

There is also a new action button `Add form helper` in the helpers list builder UI: `/admin/cohesion/helpers`.

### Bugfix: 'Breakpoint widths should not intersect.' message shown incorrectly

Fixed an issue where it was impossible to edit minimum widths on the responsive grid settings page.

### Image style usage plugin

Drupal image styles are now exported as part of DX8 Sync packages when they are detected as in use on DX8 entities.

## 5.4.0

### Component content with content moderation

You can now enable content moderation on component content entities.

### Entity update tracking for DX8 Sync

With versions prior to `5.4.0`, exports from DX8 Sync could only be imported to sites with the same version of DX8 that they were exported from.

For example, if you exported a package using DX8 Sync from a site running DX8 version `5.2`, you could not import to a site running DX8 `5.3`.

There is now an automatic entity update tracking system in place, so you will be able to export from sites running older versions of DX8 (starting with `5.4.0`) into sites running later versions.

**Note: it's not possible to export from later versions into previous versions (there will be no way to downgrade a package export). Currently there is no testing or warning when attempting this.**

### Configurable component element dropzone width

You can now control a component element dropzone width when the component is dropped onto the Layout canvas. To access these settings edit the `Dropzone` element in your component and turn on the `Dropzone width` from the `Properties` menu.

### Breakpoint indicator module

You can now enable a new sub module `DX8 breakpoint indicator` (`cohesion_breakpoint_indicator`) which provides an indicator in the bottom left of your browser window to show which breakpoint you are viewing your page at.

### SCSS Variables

It's now possible to define a list of SCSS variables in the UI (under `DX8 > Website Settings > SCSS variables`) that can be used within the style builder.

The CSS `calc()` function can also be used as a SCSS variable value, eg. `calc($var / 2)`. It's not yet possible to use SCSS variables in `Responsive grid settings`.

### Component forms - layout tabs

You can now layout component fields within tabbed sections using `Tab container` and `Tab item` fields.

Like `Accordion tabs`, tab items are nested within the tab container and can be renamed by editing them and changing the label.

The component fields that you would like in your tab should be nested within the respective tab item.

### Button and link elements -  jQuery animations

You can now add jQuery UI animation targets to `button`, `link`, `column`,`container` and `slide` elements. To access these settings, make sure the `Interaction` section is enabled and `jQuery animation` selected as the `Type`.

You can also apply animations to multiple targets at the same time.

For the `Scale` animation, `Direction` will only have an effect if a `Scale (%)` value greater than `0` is specified.

**Do not apply multiple animation to the same target as this will create undesirable effects.**

**It is recommended that the desired animations are applied prior to creating CSS styles, as these styles could have a negative impact on the jQuery animation.**

Animation effects: <http://api.jqueryui.com/category/effects>

Easing functions: <http://api.jqueryui.com/easings>

### Video component field - video preview

Allows users to preview videos when specified in component forms.

### Video and Video background elements - video preview

Allows uses to preview videos when specified in `Video` and `Video background` elements.

### Style builder - CSS3 filters

You can now add CSS3 filters to your element styles. Multiple filters can be applied at the same time.

**Note: The `filter` property is not supported in `IE11` but is supported in `Edge` and all other modern browsers.**

### Tidy Form button replaces Expand/Collapse all button

A new button added to the style builder and `Styles` tab on elements allows for easy tidying of your form.

A site builder can now click this button and remove all empty fields, breakpoint rows and sections from their styles form.

The expand/collapse all accordions button has been removed. The new tidy button replaces it on the element `Styles` tab.

### Tokenizing element title field

When you now enter a token in the `Title` field of an element, the title field will not be disabled.

### Sidebar editor - modal/backdrop z-index override

In the latest version of Drupal (`8.7.x-dev`) the `z-index` of native jQuery UI modals/backdrops has been reduced significantly, from ~`10000` to ~`600`.

To ensure no obstruction, we've hardcoded a `z-index` of ~`500` on our sidebar editor modal/backdrop.

### Bugfix: Style helpers entities not calculating dependencies on export

Previously, when exporting style helper entities via the DX8 Sync module, dependencies were not included. This has now been fixed so colors, styles, etc. are exported with style helper entities.

### Video element - Play on hover

You can now set videos to play on hover and pause when hover focus is changed.

A helper class of `coh-video-hover` is applied to the `coh-video` element when hover is active, should you want to apply hover styles to the video.

### Update to icon libraries

`Website settings > Icon libraries` page has been simplified to allow for easier uploading of icon libraries.

### Update to Font libraries

`Website settings > Font libraries` page has been simplified to allow for easier uploading of font libraries.

### Menu link element - jQuery animations

You can now add several jQuery UI animation effects to your menus. This can be changed across different breakpoints. Settings are available on the `Menu link` element.

**It is recommended that the desired animations are applied prior to creating CSS styles, as these styles could have a negative impact on the jQuery animation.**

Animation effects: <http://api.jqueryui.com/category/effects>

Easing functions: <http://api.jqueryui.com/easings>

### Support for media entity browsers

Under `DX8 > Configuration > System Settings` (`/admin/cohesion/configuration/system-settings`), the administrator can specify the type of image browser to use when selecting images for use within DX8.

You can select a different browser for editing config pages (DX8 entities, component, styles etc) and one for content pages (layout canvas fields on a node for example).

This is so you could give site builders access to use the more flexible IMCE browser and content editors access to use the entity browsers for a more simple user experience.

The available options depend on the modules you have installed. DX8 requires the [IMCE file manager](https://www.drupal.org/project/imce) module so that will always be an option.

You can also install the [Entity browser](https://www.drupal.org/project/entity_browser) module and have an image media browser available.

The entity browsers that come with the [Drupal Lighting distribution](https://www.drupal.org/project/lightning) are compatible.

**Note: because IMCE is already installed on your existing site, an update will run as part of `drush updb` to automatically set IMCE as your file browser.**

### Menu content tokens can now be used in menu templates.

Menu link entity tokens are now available in the token browser when editing a DX8 menu template. Note that tokens in menu templates only work as part of element settings and will not render out in menu template inline styles.

To add additional fields to menu links and use their tokens, we recommend using the [Menu Item Extras](https://www.drupal.org/project/menu_item_extras) module. This will expose field tokens to the DX8 token browser automatically.

This module has been added to the recommended section of the `composer.json` file as:
```
drupal/menu_item_extras
```

### Menu link item element

The `Menu link item` element has been converted to a container. If this element is empty it will default to printing the link title, otherwise it will render the child elements you place inside it.

**Note: existing elements on your site will not automatically be converted to containers and will work as normal.**

### Improved validation on responsive grid settings page

Added stricter validation to ensure that the minimum width value on each breakpoint doesn't intersect with any of the others.

### New permission for custom elements group

A new permission has been added: `DX8 Elements - Custom elements group`

This grants access to the DX8 custom elements group within the sidebar browser to the roles you specify.

### dx8-sync drush import/export directory is configurable

It's now possible to specify an import/export directory in `settings.php` when running dx8 sync from the command line.

For example:

```php
  $config_directories = [
    'dx8_sync' => 'sites/default/files/',
  ];
```

See: `modules/cohesion_sync/README.md` for more information.

### Bugfix: Icons as component field not showing

Fixed a bug where icons were not showing if used as component field in style tab.
Requires a cache clear.

### Accessibility updates

#### Keyboard navigation

Menu keyboard navigation has been improved significantly:

- `Space` = toggles child menu visibility. If no child menu, will follow link
- `Return/enter` = Always follows menu link
- `Down/right arrow keys` = opens child menu and/or moves onto next menu item
- `Up/left arrow keys` = closes child menu and/or moves onto previous menu item
- `Escape` = closes child menu and restores focus to parent menu item.

Also menu links that have sub-menus have `aria-haspopup` and `aria-expanded` attributes. The latter will toggle between a value of `true/false` depending on the display state of the child menu.

#### Button element

There is now a `button` element available within the `Interactive` section of the sidebar browser.

This shares the `Back to top` and `Scroll to` functionality of the `link` element and provides two additional `modifier` options:

- `Toggle modifier (as accessible popup - collapsed)` = adds attributes/values `aria-haspop="true"` and `aria-expanded="false"`
- `Toggle modifier (as accessible popup - expanded)` = adds attributes/values `aria-haspop="true"` and `aria-expanded="true"`

**These should be used instead of links for toggling visibility of mobile and other hidden navigation, e.g. search and language selector blocks.**

## 5.3

### Bugfix: Video controls assets not showing

Fixed a bug where the path in the .css to `/assets/video/mejs-controls.svg` was incorrect in generated css resulting in the video controls not showing for the video element.

### Component default content update

When using components that have default values, the default value will be applied at the point that the component is added to the layout canvas.

This means that later updates to default values will not propagate to components saved on nodes or in helpers etc.

Adding new fields or removing fields from a component will propagate to existing components and any new default data associated with those new fields will be added to the node when it's next saved.


### HTML base style

It is now possible to create a `HTML` base style.

This is particularly useful for maintaining `<body>` scroll position when opening a fixed position menu or modal, where the `<html>` and `<body>` elements need to be given a fixed height of `100%`.

See <https://labs.cohesiondx.com/projects/menu-page-scroll> for a demo of this technique in action.

### Layout builder support

You can now enable a new sub module `DX8 layout builder` (`cohesion_layout_builder`) which will give some support from using DX8 with the core Drupal layout builder module.

Note that the layout builder module is experimental, so this support module may break unexpectedly. Consider it experimental until the core layout builder comes out of experimental status.

Support:

- Content templates will render on the layout edit page (`node/x/layout`)
- Custom block templates can be themed with DX8 (`/admin/cohesion/templates/content_templates/block_content`)
- Tokens can be used in custom blocks.
- New "Drupal -> Content" element available in the DX8 sidebar browser (this element prints the `content` variable of the current context which effectively hands rendering the content entity over to Drupal core).

To use core layout builder with DX8, you will need to drop the new "Content" element onto your content template in place of the usual fields you would add if using the DX8 layout builder field to handle your content entity layouts.

## 5.2

### Fixed super user permissions

User #1 is now given all DX8 permissions regardless of their roles (matches how Drupal core behaves for this user).

### Link and interaction -> Modifier scope to parent element

You can now choose `Parent element` as the scope when applying using the modifier interaction type.

Once selected you can enter `Parent` (jQuery selector) which will traverse up the DOM (see `https://api.jquery.com/closest/`) to find a parent element and then looks for your given selector within that parent.

If the parent is not found (e.g. you select a non-existent class) then the modifier class will not be applied to anything.

### Row for columns - markup and style target

The `Row for columns` element consists of a two-level `<div>` container structure (`coh-row` and `coh-row-inner`).

In this update, the way additional markup and styles are added to the element has changed.

**A database update is required for existing elements to be updated.**

#### Previous behaviour

- Additional markup (classes, ids, attributes) are added to the outer container
- Custom style and style tab classes are added to the inner container

**This prevented modifiers from being added to the top-level (default) of the `Row for columns` style tree, and styles from being applied to the outer container.**

#### New behaviour

- Upon running a database update, existing `Row for columns` elements will have their custom style and style tab classes moved to the outer element
- Editing an existing `Row for columns` element or creating a new one will expose a new form section under `Settings` called `Markup and style target`, which will allow the site builder to select whether markup and styles are attached to the outer or inner container.

#### Risk mitigation

This change should pose **minimal risk** providing a database update is done. This is because markup will still be on the outer container and styles on `Row for columns` have only very recently been enabled.

**For new `Row for columns` elements, markup and styles will be added to the inner container by default.**

## 5.1

### DX8 sync

Fixed a bug where imported colors were not showing on the color palette page and would therefore be accidentally deleted.

Fixed a bug where it was possible to import two custom styles with the same class name.

### Scoping added to modifiers in Link and Interaction

When adding toggle/add/remove class modifier to a link you now have a scope option. You can choose page, component or this element. The options will scope the “target” jQuery selector you enter.

1. `Page` - Behaves as before. The Target selector will search for matches on the whole page
2. `Component` - Will traverse up parent elements from the current element until it finds the top of the component it is in. Then it searches the elements within that component for any that match the Target.
3. `This element`- Will search the current element itself and any children for the target selector. If you choose `This element` and don’t specify a target the modifier will be applied to the current element itself.

N.b. If you set the Scope as `Component` but the element is *not in a component* it will behave as if you had selected `Page`.

### composer.json

Added some additional Drupal module suggestions and updated some dependencies to the latest version.

### Element Browser Thumbnail View

The Element Browser can now be toggled into Thumbnail View.

Thumbnail images can be uploaded for components that you make and show in the Component tab in the Element browser.

A magnified "Loupe" now appears when hovering over Components that have thumbnails set in both List view and Thumbnail view.

### Component forms - column support

You can now arrange component form fields in columns. These columns will also apply to the `Component form builder` canvas.

To use this new feature, your form fields must be inside a `Field group`. Editing this will provide options for `Heading visibility` and `Column count`.

Toggling `Heading visibility` off will hide the field group's title bar and remove left/right padding - this is useful for making fields appear to be within the same form group but arranged with different column widths.

`Column count` will determine how many columns a field group's fields will divide over. By default this is set as `undefined`, meaning the number of columns will match the number of fields. There are additional options of `1`, `2`, `3` and `4`.

You can also nest field groups, though only a single level deep is recommended. This should be sufficient for most component forms.

**All form fields will stack below a display width of 768px.**

### Export packages via DX8 sync from the entity list builder pages

If DX8 Sync is enabled and the current user has the "export configuration" permission, they can now export packages for single entities from the operations dropdowns on all DX8 entity list builder pages.

### Slider container

The slider container now support most of its fields to be tokenize. Posistion outside of navigation and pagination cannot be tokenize as they are dependencies of other fields.
The same applies for autoplay and the easing section

## 5.0

### Added DX8 sync module

A new new deployment module is included in this version. See: `modules/cohesion_sync/README.md` for more details.

If you are using the `module/dx8_deployment` included in earlier releases, you will need to uninstall it before updating
to this release as the module is no longer included in the repository.


### Responsive grid conversion - float to flex

The responsive grid now uses CSS flexbox properties for layout, rather than CSS float. Flex provides advanced layout capabilities and removes the need for an extra `<div>` for vertical alignment.

#### Key changes:

- `Row for columns` and `Column` elements now use CSS flex properties.
- Clearfixes (`:before` and `:after` pseudo elements) have been removed from these elements as they interfere with several flex properties.
- Columns have been removed as target elements from `Row for columns > Match heights of children` settings. Flex does this automatically and using JS match heights with them causes unexpected behaviour.
- Styles can now be applied directly to the `Row for columns` element, either through the `Styles` tab or selecting a custom `Generic` or `Layout` style.
- `Column > Width` and `Accordion tabs container > Width of tab` settings have additional options of `Undefined (expands to available width)` and `Auto (content width)`.
- `Basic/Advanced column`, `Basic/Advanced container` and `Basic/Advanced slide` elements have been removed in favour of generic `Column`, `Container` and `Slide` elements.
- These generic elements have a single `<div>` element and no vertical alignment options like their basic ancestors.
- Layout canvas columns use flex and will be 50% opacity when width set to `None (hidden)`.
- Components on the layout canvas now have a `Configure` option in their ellipsis menu so you can jump straight to the component's edit page.

**Current basic/advanced instances of elements, styles and match heights settings will be automatically converted to generic elements following a database update, but some manual updates will be needed as below.**

#### Breaking changes:

- Columns will bleed where background styles were previously applied to advanced columns due to removal of extra `<div>`. Adding a container around the children of these elements with background styles will simulate 'advanced' behaviour.
- Vertical alignment settings on advanced columns, advanced containers and advanced slides will need to be replaced with flex equivalents (either `justify-content` for default `flex-direction: row` or `align-items` for `flex-direction: column`)
- Styles that target classes `coh-column-inner`,`coh-container-inner` and `coh-slider-item-inner` in the style tree will have no effect as these elements no longer exist.
- `float` has no effect in flex containers so `float` css property will need to be replaced with flex `order`.

## 4.0

### Split model and mapper

Increase efficiency of the layout canvas by splitting its model and mapper out of the canvas

In the following order:

An update via the `update.php` UI or `drush updb -y` is required for this change.

An import via the UI or `drush dx8 import` is required for this change.

A rebuild via the UI or `drush dx8 rebuild` is required for this change.

### IMCE image browser

The image browser now appears in a modal instead of a popup window.

### New front end contextual links for components

The front end component edit contextual links are now:

- "Edit component" (edit the instance of the component in the settings tray)
- "Configure component" (take the user to the component configuration edit page)

### Slider container

Fixes issue when nesting a Slider within a Slider and getting duplicate Pagination and Navigation on the nested Sliders

### Component content

Adds the ability to define a component with its content globally to be used and edited from any
layout canvas

An update via the `update.php` UI or `drush updb -y` `drush entup -y` is required for this change.

An import via the UI or `drush dx8 import` is required for this change.

### In use system

Fixed various issues causing the in-use system to calculate dependencies incorrectly. Changing colors, font stacks or icon libraries rebuilds only the entities used by those website settings.

Files used within a layout canvas or a WYSIWYG (including Drupal default WYSIWYG within a content entity) are tracked in the core drupal `file.usage` service.

In the following order:

1. An entity update via: `drush entup -y` is required for this change.
2. An update via the `update.php` UI or `drush updb -y` is required for this change.
3. An import via the UI or `drush dx8 import` is required for this change.
4. A rebuild via the UI or `drush dx8 rebuild` is required for this change.

### Base styles prefixed with .coh-wysiwyg

Fixed a bug where base styles prefixed with .coh-wysiwyg will no longer show incorrectly in the style preview window.

### Style editor - Float section - Clearfix toggle

The `OFF` state of the clearfix toggle didn’t previously do anything, which meant that if it was toggled on by mistake, turning it off again had no effect.

This change will now remove any `:before` and `:after` from target element, unless otherwise defined (see below).

**Backwards compatibility risk**

_‘:before' and ’:after' pseudos are unexpectedly removed from elements, causing layout issues._

This change will only remove `:before` and `:after` pseudos from styles if all of the following conditions are met:

1. The clearfix toggle is active and in the off position.
2. Custom pseudos aren’t defined in the style tree (e.g. custom string or icon).
3. A style that currently has the toggle switch active and in `OFF` position would need to be re-saved for styles to regenerate.

**Key benefits**

1. Ability to toggle clearfix on and off by breakpoint.
2. Ability to override automatic container element clearfixes with the toggle without having to define the `:before` and `:after` elements in the style tree.

### Video element ###

This element supports multiple video formats including MP4, OGG, WebM, MKV, Facebook, Vimeo and YouTube. It also provides custom video controls that can be styled consistently across multiple browsers.

This is made possible by using the `mediaelements.js` library.

For best results, a CDN-hosted video should be used rather than Facebook, Vimeo or Youtube, which still use embedded iframes rather than the native video element.

Vimeo controls can only be disabled if the host account is business or pro level, so in this case it is recommended to disable the custom video controls.

If you want the video to autoplay, you must also set the video to be muted (this is due to browser restrictions).

An import via the UI or `drush dx8 import` is required for this feature.

A rebuild via the UI or `drush dx8 rebuild` is required for this feature.

### Picture element ###

The HTML5 `picture` element provides a native solution to deliver responsive images, typically the same image of different filesize/dimensions across different breakpoints.

It contains a `srcset` element for each breakpoint with the relevant image path, plus an `img` element with the active breakpoint's image.

When it comes to styling, the `picture` element is redundant and you should only target the `img`.

**In desktop first mode, you must assign an image to the XL breakpoint.**

**In mobile first mode, you must assign an image to the XS breakpoint.**

These conditions are necessary to ensure inheritance works correctly and a `srcset` is generated for every breakpoint.
