<?php

/**
 * WP Bootstrap Navwalker
 *
 * @package WP-Bootstrap-Navwalker
 * @version 4.3.0
 */

// Check if Class Exists.
if (!class_exists('WP_Bootstrap_Navwalker')) {
    /**
     * WP_Bootstrap_Navwalker class.
     *
     * @extends Walker_Nav_Menu
     */
    class WP_Bootstrap_Navwalker extends Walker_Nav_Menu
    {
        /**
         * Starts the list before the elements are added.
         *
         * @since WP 3.0.0
         *
         * @see Walker_Nav_Menu::start_lvl()
         *
         * @param string   $output Used to append additional content (passed by reference).
         * @param int      $depth  Depth of menu item. Used for padding.
         * @param stdClass $args   An object of wp_nav_menu() arguments.
         */
        public function start_lvl(&$output, $depth = 0, $args = null)
        {
            if (isset($args->item_spacing) && 'discard' === $args->item_spacing) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $indent = str_repeat($t, $depth);
            // Default class to add to the file.
            $classes = array('dropdown-menu');
            /**
             * Filters the CSS class(es) applied to a menu list element.
             *
             * @since WP 4.8.0
             *
             * @param array    $classes The CSS classes that are applied to the menu `<ul>` element.
             * @param stdClass $args    An object of `wp_nav_menu()` arguments.
             * @param int      $depth   Depth of menu item. Used for padding.
             */
            $class_names = join(' ', apply_filters('nav_menu_submenu_css_class', $classes, $args, $depth));
            $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
            /*
             * The `.dropdown-menu` container needs to have a labelledby
             * attribute which points to it's trigger link.
             *
             * Form a string for the labelledby attribute from the the latest
             * link with an id that was added to the $output.
             */
            $labelledby = '';
            // Find all links with an id in the output.
            preg_match_all('/(<a[^>]+?)id=\"([^\"]+)\"([^>]*>)/', $output, $matches);
            // With pointer at end of array check if we got an ID match.
            if (end($matches[2])) {
                // Build a string to use as aria-labelledby.
                $labelledby = 'aria-labelledby="' . esc_attr(end($matches[2])) . '"';
            }
            $output .= "{$n}{$indent}<ul$class_names $labelledby role=\"menu\">{$n}";
        }

        /**
         * Ends the list of after the elements are added.
         *
         * @since WP 3.0.0
         *
         * @see Walker_Nav_Menu::end_lvl()
         *
         * @param string   $output Used to append additional content (passed by reference).
         * @param int      $depth  Depth of menu item. Used for padding.
         * @param stdClass $args   An object of wp_nav_menu() arguments.
         */
        public function end_lvl(&$output, $depth = 0, $args = null)
        {
            if (isset($args->item_spacing) && 'discard' === $args->item_spacing) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $indent = str_repeat($t, $depth);
            $output .= "$indent</ul>{$n}";
        }

        /**
         * Starts the element output.
         *
         * @since WP 3.0.0
         * @since WP 4.4.0 The {@see 'nav_menu_item_args'} filter was added.
         *
         * @see Walker_Nav_Menu::start_el()
         *
         * @param string   $output Used to append additional content (passed by reference).
         * @param WP_Post  $item   Menu item data object.
         * @param int      $depth  Depth of menu item. Used for padding.
         * @param stdClass $args   An object of wp_nav_menu() arguments.
         * @param int      $id     Current item ID.
         */
        public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
        {
            if (isset($args->item_spacing) && 'discard' === $args->item_spacing) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $indent = ($depth) ? str_repeat($t, $depth) : '';

            if (strcasecmp($item->attr_title, 'divider') == 0 && $depth === 1) {
                $output .= $indent . '<li role="presentation" class="dropdown-divider">';
            } elseif (strcasecmp($item->title, 'divider') == 0 && $depth === 1) {
                $output .= $indent . '<li role="presentation" class="dropdown-divider">';
            } elseif (strcasecmp($item->attr_title, 'dropdown-header') == 0 && $depth === 1) {
                $output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr($item->title);
            } elseif (strcasecmp($item->title, 'dropdown-header') == 0 && $depth === 1) {
                $output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr($item->title);
            } else {
                $classes = empty($item->classes) ? array() : (array) $item->classes;
                $classes[] = 'menu-item-' . $item->ID;

                /**
                 * Filters the arguments for a single nav menu item.
                 *
                 * @since WP 4.4.0
                 *
                 * @param stdClass $args  An object of wp_nav_menu() arguments.
                 * @param WP_Post  $item  Menu item data object.
                 * @param int      $depth Depth of menu item. Used for padding.
                 */
                $args = apply_filters('nav_menu_item_args', $args, $item, $depth);

                /**
                 * Filters the CSS class(es) applied to a menu item's list item element.
                 *
                 * @since WP 3.0.0
                 * @since WP 4.1.0 The `$depth` parameter was added.
                 *
                 * @param array    $classes The CSS classes that are applied to the menu item's `<li>` element.
                 * @param WP_Post  $item    The current menu item.
                 * @param stdClass $args    An object of wp_nav_menu() arguments.
                 * @param int      $depth   Depth of menu item. Used for padding.
                 */
                $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));

                // New addition for active class.
                $active_class = '';
                if (in_array('current-menu-item', $classes, true) || in_array('current-menu-parent', $classes, true)) {
                    $active_class = 'active';
                }

                $class_names = $class_names ? ' class="nav-item navbar-nav ' . esc_attr($class_names) . ' ' . $active_class . '"' : ' class="nav-item"';

                /**
                 * Filters the ID applied to a menu item's list item element.
                 *
                 * @since WP 3.0.1
                 *
                 * @param string   $menu_id The ID that is applied to the menu item's `<li>` element.
                 * @param WP_Post  $item    The current menu item.
                 * @param stdClass $args    An object of wp_nav_menu() arguments.
                 * @param int      $depth   Depth of menu item. Used for padding.
                 */
                $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth);
                $id = $id ? ' id="' . esc_attr($id) . '"' : '';

                $output .= $indent . '<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement"' . $id . $class_names . '>';

                $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
                $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
                $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
                $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';

                // Check if menu item has children.
                $has_children = in_array('menu-item-has-children', $classes, true);

                if ($depth === 0 && $has_children) {
                    $attributes .= ' class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"';
                } elseif ($depth === 0) {
                    $attributes .= ' class="nav-link"';
                } elseif ($depth > 0) {
                    $attributes .= ' class="dropdown-item"';
                }

                $item_output = isset($args->before) ? $args->before : '';
                $item_output .= '<a' . $attributes . '>';
                $item_output .= isset($args->link_before) ? $args->link_before : '';
                $item_output .= apply_filters('the_title', $item->title, $item->ID);
                $item_output .= isset($args->link_after) ? $args->link_after : '';
                $item_output .= '</a>';
                $item_output .= isset($args->after) ? $args->after : '';

                /**
                 * Filters a menu item's starting output.
                 *
                 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
                 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
                 * no filter for modifying the opening and closing `<li>` for a menu item.
                 *
                 * @since WP 3.0.0
                 *
                 * @param string   $item_output The menu item's starting HTML output.
                 * @param WP_Post  $item        Menu item data object.
                 * @param int      $depth       Depth of menu item. Used for padding.
                 * @param stdClass $args        An object of wp_nav_menu() arguments.
                 */
                $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
            }
        }

        /**
         * Ends the element output, if needed.
         *
         * @since WP 3.0.0
         *
         * @see Walker_Nav_Menu::end_el()
         *
         * @param string   $output Used to append additional content (passed by reference).
         * @param WP_Post  $item   Page data object. Not used.
         * @param int      $depth  Depth of page. Not Used.
         * @param stdClass $args   An object of wp_nav_menu() arguments.
         */
        public function end_el(&$output, $item, $depth = 0, $args = null)
        {
            if (isset($args->item_spacing) && 'discard' === $args->item_spacing) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $output .= "</li>{$n}";
        }
    }
}
