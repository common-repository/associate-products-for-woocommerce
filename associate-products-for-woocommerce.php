<?php
/**
 * Plugin Name: Associate products for WooCommerce
 * Description: This Plugin shows associate(related) products using shortcode. Plugin will show products using shortcode.
 * Author: Narola Infotech Solutions LLP
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version: 1.1
 * Requires at least: 4.1
 * Requires PHP: 7.2.29
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

/**
 * Associate products - ADMIN source START
 */

// Add setting link into plugin listing page
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'associate_settings_page_link');
function associate_settings_page_link($links){
	$links[] = '<a href="'. site_url() .'/wp-admin/options-general.php?page=associate-products-plugin.php">'.__('Settings').'</a>';
	return $links;
}

// display plugin settings page on the admin side
function associate_products_add_settings_page() {
    add_options_page( 'Associate products settings', 'Associate products settings', 'manage_options', 'associate-products-plugin', 'associate_plugin_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'associate_products_add_settings_page' );

// plugin settings page callback functions
function associate_plugin_render_plugin_settings_page() {
    ?>
    <h1>Associate products Plugin Settings</h1>
    <!-- <h3>Use this [associate-product] shordcode for display Associate products</h3> -->
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'associate_products_plugin_options' );
        do_settings_sections( 'associate_products_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function associate_plugin_register_settings() {
    register_setting( 'associate_products_plugin_options', 'associate_products_plugin_options', 'associate_products_plugin_options_validate' );
    add_settings_section( 'api_settings', '', 'associate_plugin_section_text', 'associate_products_plugin' );

    add_settings_field( 'associate_plugin_setting_columns', 'How many products do you want to show?', 'associate_plugin_setting_columns', 'associate_products_plugin', 'api_settings' );
}
add_action( 'admin_init', 'associate_plugin_register_settings' );

function associate_plugin_section_text() {
    echo '<h3>Use this shortcode [associate-product] for display  products</h3>';
}

function associate_plugin_setting_columns() {
    $options = get_option( 'associate_products_plugin_options' );
    echo "<input id='associate_plugin_setting_columns' name='associate_products_plugin_options[columns]' type='number' value='" . esc_attr( $options['columns'] ) . "' />";
}

/**
 * Associate products - ADMIN source END
 */


/**
 * Associate products - User source START
 */


// Enqueue styles - User side
function associate_plugin_enqueue_style() {

	wp_enqueue_style('custom-theme-css', plugin_dir_url( __FILE__ ) .'/assets/css/custom-css.css');
}
add_action( 'wp_enqueue_scripts', 'associate_plugin_enqueue_style' );

// display procts on user side
if( !function_exists('associate_product_listings') ) {

    function associate_product_listings( $atts ) {
        ob_start();
        // get current product catrgories array
        $categories_Ids = array();
        $category_array = get_the_terms( $post->ID, 'product_cat' );
        foreach ( $category_array as $term ) {
            $categories_Ids[] = $term->term_id;
        }
        
        //  fetch product per value from options
        $options = get_option( 'associate_products_plugin_options' );
        $column = $options['columns'];
        // $column = 4;
        
        // create array for fetch products
        $args = array(
            'post_type'             => 'product',
            'post_status'           => 'publish',
            'ignore_sticky_posts'   => 1,
            'posts_per_page'        => $column,
            'post__not_in'           => [get_the_ID()],
            'tax_query'             => array(
                array(
                    'taxonomy'      => 'product_cat',
                    'field' => 'term_id', 
                    'terms'         => $categories_Ids,
                    'operator'      => 'IN' 
                ),
            )
        );
        $products = new WP_Query($args);

        ?>
        <div class="associate-product-listing-wrapper">
            <h3>Associate products</h3>
            <?php
                if ($products->have_posts()) :
                    while ($products->have_posts()) : 
                        $products->the_post(); ?>
                            <div class="associate-product-wrapper">
                                <div class="associate-product-thumbnail">
                                    <a href="<?php echo get_permalink(); ?>"><div class="associate-product-thumbnail"><img src="<?php echo get_the_post_thumbnail_url(); ?>"></div></a>
                                </div>
                                <h4 class='associate-product-title'><?php echo the_title(); ?></h4>
                                <p><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
                                <a href="<?php echo get_permalink(); ?>" class="associate-product-read-more">Read more</a>
                            </div>
                        <?php                        
                    endwhile;
                    wp_reset_postdata(); 
                    else:  ?>
                <p>
                        <?php _e( 'Opps, No Products Found...!' ); ?>
                </p>            
            <?php endif; ?>
        </div>
        <?php return ob_get_clean();
    }
    add_shortcode( 'associate-product', 'associate_product_listings' );
}

/**
 * Associate products - User source END
 */

?>