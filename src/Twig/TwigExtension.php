<?php
namespace Drupal\starter\Twig;

class TwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'starter';
  }

  public function getFilters() {
    return array(
        'slugify' => new \Twig_Filter_Method($this, 'slugify'),
        'debugstrip' => new \Twig_Filter_Method($this, 'debugstrip'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('base_root', array($this, 'base_root'), array(
        'is_safe' => array('html'),
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('display_menu', array($this, 'display_menu'), array(
        'is_safe' => array('html'),
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('place_block', array($this, 'place_block'), array(
        'is_safe' => array('html'),
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('place_form', array($this, 'place_form'), array(
        'is_safe' => array('html'),
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('place_node', array($this, 'place_node'), array(
        'is_safe' => array('html'),
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('place_view', array($this, 'place_view'), array(
        'is_safe' => array('html'),
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('static_block', array($this, 'static_block'), array(
        'is_safe' => array('html'),
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('themeurl', array($this, 'themeurl'), array(
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('get_taxonomy_terms', array($this, 'get_taxonomy_terms'), array(
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
      new \Twig_SimpleFunction('get_active_theme', array($this, 'get_active_theme'), array(
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      )),
    );
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
      $manipulators = array(
        // Only show links that are accessible for the current user.
        array('callable' => 'menu.default_tree_manipulators:checkAccess'),
        // Use the default sorting of menu links.
        array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
      );
      $tree = $menu_tree->transform($tree, $manipulators);

      // Finally, build a renderable array from the transformed tree.
      $menu = $menu_tree->build($tree);

      return  array('#markup' => drupal_render($menu));
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

  public function place_view(\Twig_Environment $env, array $context, $name, $display_id = 'default') {
    $drupal = \Drupal::service('renderer');
    $view = views_embed_view($name, $display_id);
    return $drupal->render($view);
  }

  /**
   * Loads a static template block.
   *
   * @param Twig_Environment $env
   *   The twig environment instance.
   * @param array $context
   *   An array of parameters passed to the template.
   */
  public function static_block(\Twig_Environment $env, array $context, $static_block_name, $variables = array()) {
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
   *
   * Returns a string.
   */
  public function debugstrip(\Twig_Environment $env, $string) {

    if ($env->isDebug()) {
      $string = trim(strip_tags($string));
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
}