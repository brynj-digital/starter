# Drupal 8 Starter Module
A utility module for Drupal 8, adding useful generic functionality.

## Functionality

The module provides a lot of useful functionality, some of which needs configuring and some which works without any set up. Here follows a brief summary of functionality.

### User login / logout paths
The module allows the default user login / logout paths to be customised - useful for security on a Drupal site with no front-facing user roles.

### Copy role permissions
Allows a user role's permissions to be copied to another role, with the option of preserving any existing permissions in the destination role.

### Disable direct access of entities
Many content types and taxonomy vocabulary terms should not be directly accessible, for example a banner content type that is used via an entry reference on another content type. This functionality allows entities with a specific path alias (configurable) - best set up via the pathauto module - to not be viewed directly (if attempted, a 404 response is generated).

### Redirect aliased entities
If a url alias exists for an entity, the user will always be redirected to that alias (rather than viewing via /node/123, for example).

### Exclude content types / bundles from core search
Allows specific content types to be excluded from the Drupal core search.

### Pre-populate form fields via request parameters
If enabled, passing a field name and value as a request parameter will pre-populate an entity form, if a matching field is found.

### Append vocabulary label to term reference autocomplete field results
If enabled, an autocomplete taxonomy term reference field's results will have the vocabulary appended in square brackets for each match.

### Append bundle label to node reference autocomplete field results
If enabled, an autocomplete node reference field's results will have the bundle appended in square brackets for each match.

### Twig theme suggestions
Several additional theme suggestions / hooks are added by the module, based on the path alias of the current request - these are added at the page and region levels.

### Twig extensions
The following Twig extensions are provided:

 - `get_root()`: Returns the site base path.
 - `get_theme_url()`: Returns the current theme URL.
 - `display_menu(string $menu_name)`: Renders the passed menu name.
 - `place_block(string $block_name)`: Renders the passed block name.
 - `place_form(string $form_name)`: Renders the passed form class name.
 - `place_webform(string $webform_name)`: Renders the passed webform.
 - `place_node(int $node_id, string $display_type)`: Renders the passed node identifier and display type.
 - `place_view(string $view_name, int $display_id)`: Renders the passed view name and display identifier.
 - `place_menu(string $menu_name, int $min_depth, int $max_depth, string $theme)`: Renders the passed menu, with an optional min and max depth, as well as a theme (Twig template).
 - `get_taxonomy_terms(string $taxonomy_name, array $extra_fields)`: Returns an array of taxonomy terms from a taxonomy vocabulary name. You may also pass an array of the names of extra fields to pull through.
 - `get_active_theme()`: Returns the active theme name.
 - `get_image_path(string $image, string $style)`: Returns the image path for the passed image field, optionally for a specific image style.
 - `get_url_segment(int $segment, bool underscores = false)`: Returns a segment of the current request's URL. Pass true as the second parameter to convert dashes to underscores.
 - `get_current_path()`: Returns the current path.
 - `get_theme_setting(string $theme_setting)`: Returns a theme setting's value.
 - `get_variable(string $variable)`: Returns a $_GET variable.
 - `place_paragraphs(string $field_name, obj $node = null)`: Returns a rendered 'Paragraphs' field. The node will be automatically grabbed from the current route unless you specify otherwise.
 - `dd($data, bool $exit = true)`: Dumps out `$data` and exits the script, unless a second parameter of false is passed.
 - `set_meta({key: value, key: value}[, id])`: Sets meta tags, pass a second parameter to replace an already existing meta tag.
 - `get_node_path($nid)`: Returns the path of a given node ID.

### Twig filters
The following Twig filters are provided:

- `slugify`: Makes a string URL-friendly.
- `debugstrip`: Strips HTML tags and trims a string whilst Drupal's in development mode. This stops you from having to litter your template files with `|striptags|trim` which will be ineffective when in production mode.
 - `unescape`: decodes html entities in the passed string.

## Install
Use composer: `composer require brynj-digital/starter`

**Note:** Drupal Composer Project (https://github.com/drupal-composer/drupal-project) should be used.
