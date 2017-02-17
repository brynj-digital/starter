<?php
namespace Drupal\starter\Twig;

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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('base_root', [$this, 'base_root'], [
        'is_safe' => ['html'],
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('display_menu', [$this, 'display_menu'], [
        'is_safe' => ['html'],
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('place_block', [$this, 'place_block'], [
        'is_safe' => ['html'],
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('place_form', [$this, 'place_form'], [
        'is_safe' => ['html'],
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('place_node', [$this, 'place_node'], [
        'is_safe' => ['html'],
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('place_view', [$this, 'place_view'], [
        'is_safe' => ['html'],
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('static_block', [$this, 'static_block'], [
        'is_safe' => ['html'],
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('themeurl', [$this, 'themeurl'], [
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('get_taxonomy_terms', [$this, 'get_taxonomy_terms'], [
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('get_active_theme', [$this, 'get_active_theme'], [
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('get_image_path', [$this, 'get_image_path'], [
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
      new \Twig_SimpleFunction('get_path_segment', [$this, 'get_path_segment'], [
        'needs_environment' => FALSE,
        'needs_context' => FALSE,
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
  public function display_menu(\Twig_Environment $env, array $context, $menu_name) {
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
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [];
    $plugin_block = $block_manager->createInstance($block_name, $config);
    $render = $plugin_block->build();
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
   * Place a view in a Twig template with an optional display mode.
   *
   * Returns rendered view if exists, if not, null.
   */
  public function place_view(\Twig_Environment $env, array $context, $name, $display_id = 'default') {
    $drupal = \Drupal::service('renderer');
    $view = views_embed_view($name, $display_id);

    if(! is_null($view)) {
      return $drupal->render($view);
    }

    return null;
  }

  /**
   * Loads a static template block.
   *
   * @param Twig_Environment $env
   *   The twig environment instance.
   * @param array $context
   *   An array of parameters passed to the template.
   */
  public function static_block(\Twig_Environment $env, array $context, $static_block_name, $variables = []) {
    return [
      [
        '#markup' => twig_render_template(\Drupal::theme()->getActiveTheme()->getPath().'/templates/static/'.$static_block_name.'.html.twig', array_merge($context, $variables, ['theme_hook_original' => '']))
      ]
    ];
  }

  /**
   * Creates a theme URL
   *
   * @param Twig_Environment $env
   *   The twig environment instance.
   * @param array $context
   *   An array of parameters passed to the template.
   */
  public function themeurl(\Twig_Environment $env, array $context, $theme_asset) {
    return '/'.\Drupal::theme()->getActiveTheme()->getPath().$theme_asset;
  }

  /**
   * Slugifies a string.
   * Inspiration from https://gist.github.com/boboldehampsink/7354431
   */
  public function slugify($slug) {
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

    // Object structure different, depending on if node.field_name or content.field_name is passed
    if(isset($image['#items'])) {
      $image = $image['#items'];
    }
    elseif(isset($image['#item'])) {
      $image = $image['#item'];
    }

    if(!$style) {
      return $image->entity->url();
    }
    else {
      $image_style = ImageStyle::load($style);
      return $image_style->buildUrl($image->entity->getFileUri());
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
}