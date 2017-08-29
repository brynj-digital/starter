<?php
namespace Drupal\starter\Twig;

use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

class TwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'starter';
  }

  public function getFilters() {
    return [
      'slugify' => new \Twig_Filter_Method($this, 'slugify'),
      'debugstrip' => new \Twig_Filter_Method($this, 'debugstrip'),
      'unescape' => new \Twig_Filter_Method($this, 'unescape'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('base_root', [$this, 'base_root'], [
        'is_safe' => ['html'],
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('display_menu', [$this, 'place_menu'], [
        'is_safe' => ['html'],
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('place_menu', [$this, 'place_menu'], [
        'is_safe' => ['html'],
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('place_block', [$this, 'place_block'], [
        'is_safe' => ['html'],
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('place_form', [$this, 'place_form'], [
        'is_safe' => ['html'],
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('place_webform', [$this, 'place_webform'], [
        'is_safe' => ['html'],
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('place_node', [$this, 'place_node'], [
        'is_safe' => ['html'],
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('place_view', [$this, 'place_view'], [
        'is_safe' => ['html'],
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('get_theme_url', [$this, 'get_theme_url'], [
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('get_taxonomy_terms', [$this, 'get_taxonomy_terms'], [
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('get_active_theme', [$this, 'get_active_theme'], [
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('get_image_path', [$this, 'get_image_path'], [
        'needs_environment' => true,
        'needs_context' => true,
      ]),
      new \Twig_SimpleFunction('get_path_segment', [$this, 'get_path_segment'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('get_current_path', [$this, 'get_current_path'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('get_theme_setting', [$this, 'get_theme_setting'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('get_variable', [$this, 'get_variable'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('place_paragraphs', [$this, 'place_paragraphs'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('dd', [$this, 'dd'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('get_first_instance', [$this, 'get_first_instance'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('safe_merge', [$this, 'safe_merge'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),
      new \Twig_SimpleFunction('get_node', [$this, 'get_node'], [
        'needs_environment' => false,
        'needs_context' => false,
      ]),      
    ];
  }

  public function base_root(\Twig_Environment $env, array $context) {
    global $base_root;
    return $base_root;
  }

  /**
   * Provides display_menu function for page layouts.
   *
   * @param Twig_Environment $env
   *   The twig environment instance.
   * @param array $context
   *   An array of parameters passed to the template.
   */
  public function place_menu(\Twig_Environment $env, array $context, $menu_name) {
    $menu_tree = \Drupal::menuTree();

    // Build the typical default set of menu tree parameters.
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);

    // Load the tree based on this set of parameters.
    $tree = $menu_tree->load($menu_name, $parameters);

    // Transform the tree using the manipulators you want.
    $manipulators = [
      // Only show links that are accessible for the current user.
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      // Use the default sorting of menu links.
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);

    // Finally, build a renderable array from the transformed tree.
    $menu = $menu_tree->build($tree);

    return ['#markup' => drupal_render($menu)];
  }

  /**
   * Places a content block
   *
   * @param Twig_Environment $env
   *   The twig environment instance.
   * @param array $context
   *   An array of parameters passed to the template.
   */
  public function place_block(\Twig_Environment $env, array $context, $block_name) {
    $render = false;
    // try to load as plugin block
    $plugin_block = \Drupal::service('plugin.manager.block')->createInstance($block_name, $config = []);
    if(!empty($plugin_block) && $plugin_block->getPluginId() != 'broken') {
      $render = $plugin_block->build();
    }
    // get as entity block
    else {
      $block = \Drupal\block\Entity\Block::load($block_name);
      if(!empty($block)) {
        $render = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
      }
    }
    return $render;
  }

  /**
   * Places a form
   *
   * @param Twig_Environment $env
   *   The twig environment instance.
   * @param array $context
   *   An array of parameters passed to the template.
   */
  public function place_form(\Twig_Environment $env, array $context, $form_name) {
    return \Drupal::formBuilder()->getForm($form_name);
  }

  public function place_node(\Twig_Environment $env, array $context, $node_id, $node_view = 'full') {
    $node = entity_load('node', $node_id);
    if (empty($node)) {
      return '';
    }
    else {
      return node_view($node, $node_view);
    }
  }
  
  /**
   * Place a Webform submission form.
   */
  public function place_webform($webform_name) {
    if(\Drupal::moduleHandler()->moduleExists('webform')) {
      $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($webform_name);
      $webform = $webform->getSubmissionForm();
    } else {
      $webform = null;
    }

    return $webform;
  }

  /**
   * Place a view in a Twig template with an optional display mode.
   * Optionally allows parameters to be passed
   * Returns rendered view if exists, if not, null.
   */
  public function place_view($name, $display_id = 'default', $args = []) {
    $params = array_merge([$name, $display_id], $args);
    $view = call_user_func_array('views_embed_view', $params);

    if(! is_null($view)) {
      $drupal = \Drupal::service('renderer');
      return $drupal->render($view);
    }

    return null;
  }

  /**
   * Gets the current theme URL.
   */
  public function get_theme_url() {
    return '/'.\Drupal::theme()->getActiveTheme()->getPath();
  }

  /**
   * Slugifies a string.
   * Inspiration from https://gist.github.com/boboldehampsink/7354431
   */
  public function slugify($slug) {
    // Check if path auto is installed
    if(\Drupal::moduleHandler()->moduleExists('pathauto')) {
      $slug = \Drupal::service('pathauto.alias_cleaner')->cleanString($slug);
    } else {
      // Remove HTML tags
      $slug = preg_replace('/<(.*?)>/u', '', $slug);

      // Remove inner-word punctuation.
      $slug = preg_replace('/[\'"‘’“”]/u', '', $slug);

      // Make it lowercase
      $slug = mb_strtolower($slug, 'UTF-8');

      // Get the "words".  Split on anything that is not a unicode letter or number.
      // Periods are OK too.
      preg_match_all('/[\p{L}\p{N}\._-]+/u', $slug, $words);
      $slug = implode('-', array_filter($words[0]));
    }

    return $slug;
  }

  /**
   * Strips HTML tags from a string if Twig is in development mode.
   * Trims string regardless of mode.
   *
   * Returns a string.
   */
  public function debugstrip($string) {
    if (\Drupal::service('twig')->isDebug()) {
      $string = trim(strip_tags($string));
    }
    else {
      $string = trim($string);
    }

    return $string;
  }

  /**
   * Returns an array of taxonomy term names and IDs from a taxonomy vocabulary name.
   */
  public function get_taxonomy_terms(\Twig_Environment $env, array $context, $taxonomy_name, array $other_fields = null) {
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $taxonomy_name);
    $tids = $query->execute();

    $entity_manager = \Drupal::entityManager();
    $term_storage = $entity_manager->getStorage('taxonomy_term');
    $taxonomy_terms = $term_storage->loadMultiple($tids);

    $taxonomy_array = [];

    $i = 0;
    foreach($taxonomy_terms as $term) {
      $taxonomy_array[$i] = [
        'tid' => $term->get('tid')->value,
        'name' => $term->get('name')->value,
      ];

      // Add extra fields if supplied.
      if(! is_null($other_fields)) {
        foreach($other_fields as $field) {
          $taxonomy_array[$i][$field] = $term->get($field)->value;
        }
      }

      $i++;
    }

    return $taxonomy_array;
  }

  /**
   * Returns active theme name
   */
  public function get_active_theme(\Twig_Environment $env, array $context) {
    return \Drupal::theme()->getActiveTheme()->getName();
  }

  /**
   * Returns image path, optionally for a specific image size
   */
  public function get_image_path(\Twig_Environment $env, array $context, $image, $style=false) {

    // Check if $image is present
    if(is_null($image)) {
      return false;
    }

    // have we been passed the file id?
    if(is_numeric($image) && (int) $image > 0) {
      $file = File::load($image);
    }
    // have we only got an array with image target_id?
    elseif(!empty($image['target_id'])) {
      $file = File::load($image['target_id']);
    }
    // we've got an object
    else {
      // Object structure different, depending on if node.field_name, content.field_name or row._entity.field_name is passed
      if(isset($image['#items'])) {
        $image = $image['#items'];
      }
      elseif(isset($image['#item'])) {
        $image = $image['#item'];
      }

      // Check $image->entity is set
      if(!isset($image->entity)) {
        return false;
      }
      else {
        $file = $image->entity;
      }
    }

    // we've got a valid image file
    if(!empty($file)) {
      // return original image file
      if(!$style) {
        return $file->url();
      }
      // return specific image size
      else {
        $image_style = ImageStyle::load($style);

        // if the image style doesn't exist, return the normal image
        if(is_null($image_style)) {
          return $file->url();
        }

        return $image_style->buildUrl($file->getFileUri());
      }
    }
    else {
      return false;
    }
  }

  /**
   * Get path segment from current request.
   * @param int $segment - starting from 1 being the first section of the url after the first forward slash
   * @param bool $underscores - convert dashes to underscores
   * @return string of path segment or null
   */
  public function get_path_segment($segment, $underscores = false) {
    // Reduce segment index by 1 to account for array key starting with 0
    $segment--;
    $path = \Drupal::request()->getPathInfo();
    $segments = explode('/', trim($path, '/'));

    if(isset($segments[$segment])) {
      if($underscores) {
        return str_replace('-', '_', $segments[$segment]);
      }

      return $segments[$segment];
    }
    else {
      return null;
    }
  }

  /**
   * Returns the current path.
   */
  public function get_current_path() {
    $current_path = \Drupal::service('path.current')->getPath();
    $result = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);

    return $result;
  }

  /**
   * Returns a theme setting.
   */
  public function get_theme_setting($theme_setting) {
    return theme_get_setting($theme_setting);
  }

  /**
   * Return a $_GET variable.
   */
  public function get_variable($variable) {
    return \Drupal::request()->get($variable);
  }

  /**
   * Return a rendered 'Paragraphs' field.
   */
  public function place_paragraphs($field_name, $node = null) {

    // Check if the 'Paragraphs' module exists.
    if(! \Drupal::moduleHandler()->moduleExists('paragraphs')) {
      return false;
    }

    // If $node isn't passed, let's try and get it ourselves.
    if($node === null) {
      $node = \Drupal::routeMatch()->getParameter('node');
    }

    if ($node) {
      if($node->hasField($field_name)) {
        $field_definitions = $node->getFieldDefinitions();
        $field_config = $field_definitions[$field_name];

        $config = [
          'field_definition' => $field_config,
          'settings' => $field_config->getSettings(),
          'label' => $field_config->label(),
          'view_mode' => 'full',
          'third_party_settings' => [],
        ];

        $formatter = \Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter::create(\Drupal::getContainer(), $config, NULL, NULL);

        // Required by EntityReferenceFormatterBase
        // getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode)
        $parts = $node->get($field_name);
        foreach ($parts as $delta => $item) {
          $item->_loaded = true;
        }

        $language_manager = \Drupal::service('language_manager');
        $languageId = $language_manager->getCurrentLanguage()->getId();
        $elements = $formatter->viewElements($parts, $languageId);

        return $elements;
      }
    }
  }

  /**
   * More efficient than kint(), and will exit script.
   */
  public function dd($data, $exit = true) {
    echo dump($data);

    if($exit) {
      return exit;
    }

    return false;
  }

  /**
   * Returns the first instance of a field's value from an array of fields.
   */
  public function get_first_instance($fields, $rows) {

    if(empty($rows)) {
      return null;
    }

    foreach($rows[0]['#rows'] as $row) {
      $entity = $row['#row']->_entity;

      foreach($fields as $field) {
        if($entity->hasField($field)) {
          $value = $entity->get($field)->getValue();

          if(! empty($value)) {
            return $value;
          }
        }
      }
    }
    return null;
  }

  /*
   * Html entity decode the passed string
   */
  public function unescape($html) {
      return html_entity_decode($html);
  }

  /*
   * Merges arrays or objects - source object / array is appended to destination object / array
  */
  public function safe_merge($destination, $source) {

    if(!is_object($source)) {
      $source = (object) $source;
    }
    $object_properties = get_object_vars($source);
    if(is_object($destination)) {
      foreach($object_properties as $property => $value) {
        $destination->{$property} = $value;
      }
    }
    else {
      foreach($object_properties as $property => $value) {
        $destination[$property] = $value;
      }
    }
    return $destination;
  }

  /**
   * Return the node via the current path.
   */
  public function get_node() {
    return \Drupal::routeMatch()->getParameter('node');
  }
}
