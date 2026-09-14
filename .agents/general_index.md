# Site Studio (Cohesion) - File Index

> **Project**: Acquia Site Studio (formerly Cohesion) - A visual website builder for Drupal
> **Version**: 8.x-8.2.x
> **Type**: Drupal Module with React Frontend and Node.js micro Service for generating styles and templates

---

## Root Level Files

### Core Module Files
- **cohesion.info.yml** - Drupal module metadata defining Site Studio core module, dependencies, and version requirements for Drupal 10.2+/11
- **cohesion.module** - Main module file containing hooks, constants, and core functionality for Site Studio
- **cohesion.install** - Installation, update, and uninstallation hooks for database schema and configuration
- **cohesion.api.php** - API documentation file defining hooks available to other modules
- **cohesion.services.yml** - Dependency injection service definitions for Site Studio services
- **cohesion.routing.yml** - Defines URL routes for admin interfaces, API endpoints, and entity management
- **cohesion.permissions.yml** - Permission definitions for access control to Site Studio features
- **cohesion.libraries.yml** - Frontend asset library definitions (CSS/JS) for admin UI and frontend
- **cohesion.links.menu.yml** - Admin menu links for Site Studio configuration and management interfaces
- **cohesion.breakpoints.yml** - Responsive breakpoint definitions for layout system
- **cohesion.field_type_categories.yml** - Field type categorization for form builder

### Build & Development Files
- **composer.json** - PHP dependency management including entity_reference_revisions, imce, token, ckeditor5
- **composer.dev.json** - Development dependencies for testing and code quality tools
- **package.json** - Node.js dependencies for React frontend build system
- **package-lock.json** - Locked versions of npm dependencies for reproducible builds
- **Gulpfile.js** - Task runner for building and compiling frontend assets
- **phpcs.xml** - PHP Code Sniffer configuration for code quality standards
- **behat.yml** - Behat configuration for behavior-driven testing
- **docker-compose.yml** - Docker container configuration for local development environment
- **.lando.base.yml** - Lando configuration for local Drupal development
- **acquia-pipelines.yml** - Acquia Cloud CI/CD pipeline configuration
- **.stylelintrc.json** - StyleLint configuration for CSS/SCSS linting

### Documentation
- **README.md** - Installation instructions, composer setup, drush commands, and upgrade procedures
- **CONTRIBUTING.md** - Guidelines for contributing to Site Studio development
- **RELEASE_NOTES.md** - Version history and changelog for releases
- **LICENSE** - GPL-2.0-or-later license file

### Configuration
- **.gitignore** - Git ignore patterns for build artifacts and dependencies
- **sonar-project.properties** - SonarQube code analysis configuration
- **public-coveralls.yml** - Code coverage reporting configuration

---

## Main Source Directory (`src/`)

### Core API & Client
- **CohesionApiClient.php** - HTTP client for communicating with Site Studio API service for style building and template rendering
- **ApiUtils.php** - Utility functions for API request/response handling and validation
- **ApiPluginBase.php** - Base class for API endpoint plugins
- **ApiPluginInterface.php** - Interface defining API plugin contract
- **ApiPluginManager.php** - Plugin manager for discovering and instantiating API plugins
- **CohesionJsonResponse.php** - Custom JSON response class for API endpoints

### Entity Management
- **CohesionEntityViewBuilder.php** - Entity view builder for rendering Site Studio entities with layout data
- **CohesionListBuilder.php** - List builder for admin UI listing of Site Studio entities
- **CohesionHtmlRouteProvider.php** - Route provider for entity CRUD operations
- **CohesionLayoutRevisionManager.php** - Manages entity revisions for layout canvases
- **CohesionLayoutFieldProcessor.php** - Processes layout field data for rendering and storage
- **CohesionApiElementStorage.php** - Storage handler for element definitions from API
- **EntityJsonValuesTrait.php** - Trait for entities that store JSON configuration data
- **EntityHasResourceObjectTrait.php** - Trait for entities with API resource objects
- **TemplateEntityTrait.php** - Trait for entities that generate Twig templates
- **EntityUpdateInterface.php** - Interface for entity update plugins
- **EntityUpdateManager.php** - Manages entity updates and migrations between versions
- **EntityUpdatePluginInterface.php** - Interface for entity update plugins
- **EntityUpdatePluginManager.php** - Plugin manager for entity update system

### Plugin Systems
- **EntityGroupsPluginBase.php** - Base class for entity grouping/categorization plugins
- **EntityGroupsPluginInterface.php** - Interface for entity grouping plugins
- **EntityGroupsPluginManager.php** - Manages plugins that organize entities into groups
- **ImageBrowserPluginBase.php** - Base class for image browser integration plugins
- **ImageBrowserPluginInterface.php** - Interface for image browser plugins
- **ImageBrowserPluginManager.php** - Manages image browser plugins (IMCE, Media Library, etc.)
- **ImageBrowserUpdateManager.php** - Handles updates to image browser configurations
- **UsagePluginBase.php** - Base class for entity usage tracking plugins
- **UsagePluginInterface.php** - Interface for usage tracking plugins
- **UsagePluginManager.php** - Manages plugins that track where entities are used
- **UsageUpdateManager.php** - Updates usage tracking records
- **StylesApiPluginBase.php** - Base class for style API endpoint plugins

### Services & Utilities
- **CohesionServiceProvider.php** - Service container modifications for Site Studio
- **CohesionResourceBase.php** - Base REST resource class for API endpoints
- **CohesionSupportUrl.php** - Helper for generating support/documentation URLs
- **SettingsEndpointUtils.php** - Utilities for settings-related API endpoints
- **ExceptionLoggerTrait.php** - Trait for logging exceptions with context
- **MediaLibraryCohesionOpener.php** - Media library integration for Site Studio UI

### Template Storage
- **TemplateStorage/** - Directory containing template storage handlers and managers
  - Handles compilation and caching of Twig templates generated from layout canvases

---

## Subdirectories in `src/`

### `Annotation/`
- Contains annotation classes for plugin discovery and metadata

### `Config/`
- Configuration entity classes and configuration management utilities

### `Controller/`
- Admin UI controllers, AJAX endpoints, and page controllers for Site Studio interfaces

### `Drush/`
- Drush command definitions for rebuild, import, export, and sync operations

### `Element/`
- Render element classes for custom form elements and UI components

### `Entity/`
- Entity type definitions (Components, Styles, Templates, Helpers, etc.)

### `Event/`
- Event classes for Site Studio's event system

### `EventSubscriber/`
- Event subscribers for responding to Drupal and Site Studio events

### `Form/`
- Form classes for admin configuration interfaces and entity forms

### `LayoutCanvas/`
- Classes for managing layout canvas data structure and operations

### `Plugin/`
- Plugin implementations (Usage, EntityUpdate, Api, ImageBrowser, etc.)

### `Render/`
- Custom render arrays and rendering utilities

### `Routing/`
- Dynamic route providers and route subscribers

### `Services/`
- Service classes for core functionality (LocalFilesManager, ResponsiveGridSettings, etc.)

### `StreamWrapper/`
- Custom stream wrappers for Site Studio asset management

### `TwigExtension/`
- Twig extensions for custom filters, functions, and template tags

---

## Submodules (`modules/`)

### Core Submodules
- **cohesion_base_styles/** - Base style definitions (typography, colors, buttons)
- **cohesion_custom_styles/** - Custom CSS style manager
- **cohesion_elements/** - Component and element system
- **cohesion_style_guide/** - Living style guide generator
- **cohesion_style_helpers/** - Reusable style helper classes
- **cohesion_sync/** - Configuration import/export and sync
- **cohesion_templates/** - Template management system
- **cohesion_website_settings/** - Global site settings (colors, fonts, breakpoints)
- **sitestudio_page_builder/** - Visual page builder interface

### Integration & Extension Modules
- **sitestudio_jsonapi/** - JSON:API integration for headless/decoupled architectures
- **sitestudio_contenthub/** - Acquia Content Hub integration
- **sitestudio_data_transformers/** - Data transformation utilities
- **sitestudio_governance/** - Governance and permissions enhancement
- **sitestudio_claro/** - Claro admin theme integration
- **sitestudio_legacy_ckeditor/** - Legacy CKEditor 4 support
- **cohesion_breakpoint_indicator/** - Visual breakpoint indicator tool

### Example/Developer Modules
- **example_custom_component/** - Example custom component implementation
- **example_custom_select/** - Example custom select field implementation
- **example_element/** - Example custom element plugin

---

## Frontend Application (`apps/`)

### React Application (`apps/react/`)
The main React application powering the Site Studio visual editor and admin interfaces

#### Core Apps
- **src/apps/Editor/EditorApp.js** - Main visual page builder editor application
- **src/apps/StyleGuideManager/StyleGuideManager.js** - Style guide management interface
- **src/apps/Sync/Store.js** - Configuration sync application state management

#### Editor Components (`src/components/editor/`)
- **CanvasRenderer/CanvasRenderer.js** - Renders the visual canvas with drop zones and components
- **ComponentToolbar/ComponentToolbar.js** - Toolbar with component actions (edit, delete, duplicate)
- **DrupalInterface/DrupalInterface.js** - Bridge between React app and Drupal backend
- **EditInPlace/** - In-place editing components for text and WYSIWYG fields
- **LayoutCanvasContainer/** - Container managing the layout canvas state and interactions
- **SidebarBrowser/SidebarBrowser.js** - Component browser sidebar
- **SidebarEditor/SidebarEditor.js** - Component settings editor sidebar
- **PageBuilderToolbar/** - Main toolbar for page builder with save, preview, undo/redo

#### Form Renderer (`src/components/formRenderer/`)
- **FormBuilder.js** - Dynamic form builder from JSON schemas
- **ColorPicker.js** - Color picker field with palette support
- **EntityBrowser/** - Drupal entity browser integration
- **FileBrowser/** - File and image browser components
- **LayoutCanvas/** - Tree-based layout canvas editor
- **StyleEditor/** - CSS style property editor
- **DecisionTree/** - Conditional logic builder for form visibility

#### Common Components (`src/components/common/`)
- **BladesMenu/** - Hierarchical blade navigation interface
- **DropdownMenu/** - Dropdown menu components
- **Modal.js** - Modal dialog component
- **Toast.js** - Toast notification system
- **LoadingSpinner/** - Loading state indicators
- **ColorPicker/** - Advanced color picker with gradients

#### Utilities (`src/utils/`)
- **drupalSettings.js** - Interface to Drupal settings object
- **ssa-layout-canvas.js** - Layout canvas utilities and operations
- **ssa-form-builder.js** - Form builder utilities
- **component-form.js** - Component form helpers
- **decisionTreeEvaluator.js** - Evaluates conditional logic trees

#### State Management (`src/reducers/`)
- **editedNodeReducer.js** - Manages currently edited node state
- **formBuilderReducer.js** - Form state management
- **layoutCanvasReducer.js** - Canvas operations (add, move, delete components)
- **mapperReducer.js** - Data mapping state
- **uiReducer.js** - UI state (sidebar open/closed, modals, etc.)

#### Contexts (`src/contexts/`)
- **editor/** - React contexts for editor state sharing across components

#### Webpack Configuration
- **webpack.dev.js** - Development build configuration with hot module reloading
- **webpack.production.js** - Production build configuration with optimization
- **webpack-lru-cache-shim.js** - Shim for LRU cache in webpack builds

---

## Asset Services (`cohesion-services/`)

### dx8-gateway
Gateway service providing element definitions, templates, and forms to Site Studio

#### Assets
- **assets/base-styles/** - JSON definitions for default base styles (headings, body, buttons, lists)
- **assets/custom-styles/** - JSON definitions for custom style categories
- **assets/elements/** - Element type definitions (container, column, heading, image, etc.)
- **assets/elements-forms/** - JSON form schemas for element configuration
- **assets/elements-properties/** - Property definitions for element settings
- **assets/elements-categories/** - Element organization into categories (layout, content, media, etc.)
- **assets/element-templates/** - Twig templates for rendering elements
- **assets/element-templates/js/** - JavaScript for element interactivity (sliders, tabs, maps, etc.)
- **assets/canvas/** - Canvas editor configurations
- **assets/component/** - Component system configurations
- **assets/component-content/** - Component content field definitions
- **assets/custom-component/** - Custom component configuration
- **assets/dx8/scripts/** - Compiled React application bundles

---

## Configuration (`config/`)

### Cohesion Configuration Entities
- **cohesion/** - Exportable configuration for Site Studio entities (components, styles, templates)
- **sync/** - Drupal configuration sync directory
- **eudaimonia/** - Additional configuration set (possibly for testing or demo)

---

## Static Assets

### `css/` - Compiled CSS stylesheets for admin UI
### `js/` - JavaScript files for admin interfaces and element behaviors
### `scss/` - Source SCSS files for styling
### `images/` - Icons, logos, and UI images
### `themes/` - Theme-related assets
### `templates/` - Twig template files for admin UI

---

## Testing

### `tests/` - PHP unit and functional tests
### `e2e-tests/` - End-to-end testing with Playwright or similar

---

## CI/CD & DevOps

### `.github/workflows/` - GitHub Actions workflow definitions for automated testing and deployment
### `.acquia/` - Acquia Cloud-specific configuration and hooks
### `.vscode/` - VS Code workspace settings for development

---

## Summary

This is **Acquia Site Studio** (formerly Cohesion), a powerful visual website builder for Drupal that combines:
- **Backend**: Drupal module system with entity types, plugins, and services
- **Frontend**: React-based visual editor with drag-and-drop layout building
- **API Service**: Node.js gateway serving element definitions and compiling styles
- **Submodules**: Specialized modules for styles, templates, elements, and sync
- **Asset Pipeline**: Build system compiling React, SCSS, and generating optimized bundles

The system enables non-technical users to build responsive Drupal sites visually while providing developers with extensible plugin systems and custom element capabilities.

