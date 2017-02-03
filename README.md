# Drupal 8 Starter Module
A utility module for Drupal 8, adding useful generic functionality.

## Functionality

The module provides a lot of useful functionality, some of which needs configuring and some which works without any set up. Here follows a brief summary of functionality.

### User login / logout paths
The module allows the default user login / logout paths to be customised - useful for security on a Drupal site with no front-facing user roles.

### Copy role permissions
Allows a user role's permissions to by copied to another role, with the option of preserving any existing permissions in the destination role.

### Disable direct access of entities
Many content types and taxonomy vocabulary terms should not be directly accessible, for example a banner content type that is used via an entry reference on another content type. This functionality allows entities with a specific path alias (configurable) - best set up via the pathauto module - to not be viewed directly (if attempted, a 404 response is generated).

### Redirect aliased entities
If a url alias exists for an entity, the user will always be redirected to that alias (rather than viewing via /node/123, for example).

### Exclude content types / bundles from core search
Allows specific content types to be excluded from the Drupal core search.

### Pre-populate form fields via request parameters
If enabled, passing a field name and value as a request parameter will pre-populate an entity form, if a matching field is found.

### Twig theme suggestions
Several additional theme suggestions / hooks are added by the module, based on the path alias of the current request - these are added at the page and region levels.

### Twig extensions
The following Twig extensions are provided:

 - `base_root()`: outputs the site base path.
 - `display_menu($menu_name)`: renders the passed menu name.
 - `place_block($block_name)`: renders the passed block name.
 - `place_form($form_name)`: renders the passed form class name.
 - `place_node($node_id, $display_type)`: renders the passed node identifier and display type.
 - `place_view($view_name, $display_id)`: renders the passed view name and display identifier.
 - `get_taxonomy_terms($taxonomy_name, array $extra_fields)`: returns an array of taxonomy terms from a taxonomy vocabulary name. You may also pass an array of the names of extra fields to pull through.
 - `get_active_theme()`: returns the active theme name
 - `get_image_path($image, $style)`: returns the image path for the passed image field, optionally for a specific image style 

### Twig filters
The following Twig filters are provided:

- `slugify`: makes a string URL-friendly.
- `debugstrip`: strips HTML tags and trims a string whilst Drupal's in development mode. This stops you from having to litter your template files with `|striptags|trim` which will be ineffective when in production mode.

## Install
Use composer: `composer require brynj-digital/starter`

**Note:** Drupal Composer Project (https://github.com/drupal-composer/drupal-project) should be used.
