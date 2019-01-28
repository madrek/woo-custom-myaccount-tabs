<?php
add_action( 'wp_enqueue_scripts', 'enqueue_load_fa' );
function enqueue_load_fa() {
    wp_enqueue_style( 'load-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );
 
}

function show_product_categories($atts, $content = null) {
	/*extract(shortcode_atts(array(
		'cat_slug' => '',
		'product_per_page' => 12,
		'columns' => 3
	), $atts));*/

	$terms = get_terms( array(
							'taxonomy' => 'product_cat',
							'hide_empty' => false,
							'parent' => 0,
							'orderby' => 'name',
							'exclude' => array(203,320,199,193,207,196,338,200,293,217,299,238,197,336,198,205,194,204,218,206,201,202,337,195,300)
						) );
	
	ob_start();
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		echo '<ul class="category-list clearfix">';
		foreach($terms as $term) {
			$thumbnail_id = get_woocommerce_term_meta($term->term_id, 'thumbnail_id', true);
			// get the image URL
			$image = wp_get_attachment_url($thumbnail_id);
			echo '<li>
					<div class="category-wrap">
						<a href="'.esc_url( get_term_link( $term ) ).'"><img src="'.$image.'" alt="'.$term->name.'" /></a>
						<a class="cat_title" href="'.esc_url( get_term_link( $term ) ).'"><span class="cat-arrow"></span> '.$term->name.'</a>
					</div>
				</li>';
		}
		echo '</ul>';
	}
	$content = ob_get_clean();
	return $content;
}
add_shortcode('show_product_categories', 'show_product_categories');

// enqueue the flexslider scripts and styles
function lis_enqueue_scripts() {
    wp_enqueue_script( 'flexslider-script', get_stylesheet_directory_uri().'/js/jquery.flexslider-min.js', array(), '1.0.0', true );
    wp_enqueue_style( 'flexslider-style', get_stylesheet_directory_uri().'/flexslider.css' );
	if( is_product_category() ) {
		wp_enqueue_script( 'datatables-script', 'https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js', array(), '1.0.0', true );
		wp_enqueue_style( 'datatables-style', 'https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'lis_enqueue_scripts' );

function load_custom_script_footer() {
	if( is_front_page() ) {
?>
	<script type="text/javascript">
        jQuery(window).load(function(){
            jQuery('.flexslider').flexslider({
                animation: "fade",
				controlNav: true,
                start: function(slider){
                    jQuery('body').removeClass('loading');
                }
            });
        });
    </script>
<?php
	}
	
	if( is_product_category() ) {
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('#products_table').DataTable();
		} );
    </script>
<?php
	}
?>
	<script type="text/javascript">
		jQuery(function(){
			jQuery(window).scroll(function() {
				if(jQuery(this).scrollTop() >= 250) {
					jQuery('#main-header').addClass('stickytop');
					jQuery('#et-main-area').addClass('sticked');
				}
				else {
					jQuery('#main-header').removeClass('stickytop');
					jQuery('#et-main-area').removeClass('sticked');
				}
			});
			
			if( jQuery(".single-product .wc-tabs").length ) {
				//
			} else {
				jQuery("#product-custom-tabs").css("padding-top", 0);
			}

			jQuery(".tax-product_cat select[name=sub_categories]").on( "change", function() {
				var go_to = jQuery(this).val();
				if(go_to != '') {
					window.location = go_to;
				}
			});
		});
	</script>
<?php
}
add_action('wp_footer', 'load_custom_script_footer', 50);

function load_head_script() {
	if( is_product() ) {
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			var tabs = jQuery('.wc-tabs li').length;
			jQuery('.wc-tabs').addClass('tabs-'+tabs);
			
			jQuery(".shop_attributes td:empty").parent("tr").hide();
			jQuery(".woocommerce table.shop_attributes th").css("width", "250px");
		} );
    </script>
<?php
	}
}
add_action('wp_head', 'load_head_script');

function get_featured_products() {
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => 5,
		'orderby'   => 'menu_order',
		'order'     => 'ASC',
		'meta_query' => array(
			array(
				'key'     => '_featured',
				'value'   => 'yes',
				'compare' => '=',
			),
		),
	);

	$query = new WP_Query( $args );
	$output = '';
	if($query->have_posts()) {
		$output .= '<div class="flexslider">
						<ul class="slides">';
		global $post;
		$product_cat_id = 9;
		while ( $query->have_posts() ) {
			$query->the_post();
			$output .= '<li>';
			if ( has_post_thumbnail() ) {
				$output .= '<div class="product-image">'.get_the_post_thumbnail( $post->ID, 'large' ).'</div>';
			}
			
			$terms = get_the_terms( $post->ID, 'product_cat' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ($terms as $term) {
					$product_cat_id = $term->term_id;
					break;
				}
			}
			$output .= '<div class="product-info">
							<div class="product-title">'.get_the_title().'</div>
							<div class="prop_desc">'.$post->post_excerpt.'</div>
							<div class="slide-buttons">
								<a href="'.get_permalink($post->ID).'" class="view-product"><span class="prod-arrow"></span> VIEW THIS PRODUCT</a>
								<a href="'.esc_url( get_term_link( $product_cat_id ) ).'" class="view-related"><span class="cat-arrow"></span> VIEW SIMILAR PRODUCTS</a>
							</div>
						</div>
						</li>';
		}
		wp_reset_postdata();
		$output .= "</ul></div>";
	}
	return $output;
}
add_shortcode('featured_products_slider', 'get_featured_products');

/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */
if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
	require_once dirname( __FILE__ ) . '/cmb-field-select2/cmb-field-select2.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
	require_once dirname( __FILE__ ) . '/cmb-field-select2/cmb-field-select2.php';
}

/**
 * CMB2 Field Type: Select2 asset path
 *
 * Filter the path to front end assets (JS/CSS).
 */
function pw_cmb2_field_select2_asset_path() {
	return get_stylesheet_directory_uri() . '/cmb-field-select2';
}
add_filter( 'pw_cmb2_field_select2_asset_path', 'pw_cmb2_field_select2_asset_path' );

add_action( 'cmb2_admin_init', 'lis_register_pdf_metabox' );
/**
 * Hook in and add metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
 */
function lis_register_pdf_metabox() {
	$prefix = '_cmb_';
	/**
	 * Sample metabox to demonstrate each field type included
	 */
	$cmb_ins = new_cmb2_box( array(
		'id'            => $prefix . 'pdf_specs',
		'title'         => esc_html__( 'PDF Specifications', 'cmb2' ),
		'object_types'  => array( 'pdfs' ), // Post type
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'PDF', 'cmb2' ),
		'desc' => esc_html__( 'Upload a PDF or enter a URL.', 'cmb2' ),
		'id'   => $prefix . 'lis_pdf',
		'type' => 'file',
	) );
	$cmb_ins->add_field( array(
		'name'    => 'User Access',
		'id'      => $prefix . 'lis_user_access',
		'desc'    => 'Select users who can access this file.',
		'type'    => 'pw_multiselect',
		'options' => get_lis_blog_users(),
	) );
	$cmb_ins->add_field( array(
		'name'       => esc_html__( 'Date', 'cmb2' ),
		'desc'       => esc_html__( 'File upload date', 'cmb2' ),
		'id'         => $prefix . 'pdf_upload_date',
		'type'       => 'text_date',
	) );
}

function get_lis_blog_users() {
	$blogusers = get_users();
	$users = array();
	// Array of WP_User objects.
	foreach ( $blogusers as $user ) {
		if($user->first_name && $user->last_name) {
			$users[$user->ID] = $user->first_name.' '.$user->last_name;
		} else {
			$users[$user->ID] = $user->display_name;
		}
	}
	return $users;
}

add_action( 'init', 'register_custompost_type' );
function register_custompost_type() {
  $pdf_labels = array(
		'name' => _x('Service Report', 'Service Report name', 'Divi'),
		'singular_name' => _x('Service Report', 'Service Report type singular name', 'Divi'),
		'add_new' => _x('Add New', 'Service Report', 'Divi'),
		'add_new_item' => __('Add New Service Report', 'Divi'),
		'edit_item' => __('Edit Post', 'Divi'),
		'new_item' => __('New Service Report', 'Divi'),
		'view_item' => __('View Post', 'Divi'),
		'search_items' => __('Search Service Report', 'Divi'),
		'not_found' => __('No Service Report Found', 'Divi'),
		'not_found_in_trash' => __('No Service Report Found in Trash', 'Divi'),
		'parent_item_colon' => ''
	);

	register_post_type('pdfs', array('labels' => $pdf_labels,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'capability_type' => 'post',	
		    //'menu_icon' => 'dashicons-video-alt3',	
			'hierarchical' => false,
			'publicly_queryable' => true,
			'query_var' => true,
			'exclude_from_search' => false,
			'rewrite' => array('slug' => 'pdf'),
			'show_in_nav_menus' => false,
			'supports' => array('title', 'page-attributes')
		)
	);
}

add_action( 'cmb2_admin_init', 'lis_register_product_metabox' );
/**
 * Hook in and add metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
 */
function lis_register_product_metabox() {
	$prefix = '_cmb_';
	/**
	 * Sample metabox to demonstrate each field type included
	 */
	$cmb_ins = new_cmb2_box( array(
		'id'            => $prefix . 'product_specs',
		'title'         => esc_html__( 'Product Specifications', 'cmb2' ),
		'object_types'  => array( 'product' ), // Post type
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Brand', 'cmb2' ),
		'desc' => esc_html__( 'Ex: CEM', 'cmb2' ),
		'id'   => $prefix . 'product_brand',
		'type' => 'text_medium',
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Capacity', 'cmb2' ),
		'desc' => esc_html__( 'Ex: 100g', 'cmb2' ),
		'id'   => $prefix . 'product_capacity',
		'type' => 'text_medium',
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Readability', 'cmb2' ),
		'desc' => esc_html__( 'Ex: 0.01g', 'cmb2' ),
		'id'   => $prefix . 'product_readability',
		'type' => 'text_medium',
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Pan Size', 'cmb2' ),
		'desc' => esc_html__( 'Ex: 90mm', 'cmb2' ),
		'id'   => $prefix . 'product_pan_size',
		'type' => 'text_medium',
	) );
	$cmb_ins->add_field( array(
		'name'    => esc_html__( 'Specifications', 'cmb2' ),
		'desc'    => esc_html__( '', 'cmb2' ),
		'id'      => $prefix . 'product_specifications',
		'type'    => 'wysiwyg',
		'options' => array( 'textarea_rows' => 5, ),
	) );
	$cmb_ins->add_field( array(
		'name'    => esc_html__( 'Electrical Requirements', 'cmb2' ),
		'desc'    => esc_html__( '', 'cmb2' ),
		'id'      => $prefix . 'electrical_requirements',
		'type'    => 'wysiwyg',
		'options' => array( 'textarea_rows' => 5, ),
	) );
	/*$cmb_ins->add_field( array(
		'name'       => esc_html__( 'Video', 'cmb2' ),
		'desc'       => esc_html__( 'Video URL', 'cmb2' ),
		'id'         => $prefix . 'product_video_url',
		'type'       => 'text',
	) );*/
	$cmb_ins->add_field( array(
		'name'    => esc_html__( 'Videos', 'cmb2' ),
		'desc'    => esc_html__( 'Add video links', 'cmb2' ),
		'id'      => $prefix . 'product_video_url',
		'type'    => 'wysiwyg',
		'options' => array( 'textarea_rows' => 5, ),
	) );
	$cmb_ins->add_field( array(
		'name'    => esc_html__( 'Recommended Suppliers', 'cmb2' ),
		'desc'    => esc_html__( '', 'cmb2' ),
		'id'      => $prefix . 'recommended_suppliers',
		'type'    => 'wysiwyg',
		'options' => array( 'textarea_rows' => 5, ),
	) );
	$cmb_ins->add_field( array(
		'name'    => esc_html__( 'Manuals', 'cmb2' ),
		'desc'    => esc_html__( 'Add manual links', 'cmb2' ),
		'id'      => $prefix . 'product_manuals',
		'type'    => 'wysiwyg',
		'options' => array( 'textarea_rows' => 5, ),
	) );
}

// Removes related products
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

// Removes excerpts
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_content', 20 );
function woocommerce_output_product_content() {
	global $post, $product;
	$content_product = get_post($post->ID);
	$content_product_text = $content_product->post_content;
	$content_product_text = apply_filters('the_content', $content_product_text);

	echo '<div class="product-desc">'.$content_product_text.'</div>';
}

add_filter( 'woocommerce_product_tabs', 'lis_woo_remove_product_tabs', 98 ); 
function lis_woo_remove_product_tabs( $tabs ) {
	global $product;
    unset( $tabs['description'] ); // Remove the description tab
	//unset( $tabs['additional_information'] );
	if( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) {
		$tabs['additional_information']['title'] = __( 'Details' );
	}
	$num_comments = get_comments_number($product->id);
	if ( $num_comments > 0 ) {
		$tabs['reviews']['title'] = __( 'Customer Reviews' );
		$tabs['reviews']['priority'] = 58;
	}

    return $tabs;
}

add_filter( 'woocommerce_product_tabs', 'lis_woo_new_product_tabs' );
function lis_woo_new_product_tabs( $tabs ) {
	global $post;
	$product_id = $post->ID;

	if(get_post_meta($product_id, '_cmb_product_specifications', true)){
		$tabs['product_specifications_tab'] = array(
			'title' 	=> __( 'Specifications', 'Divi' ),
			'priority' 	=> 53,
			'callback' 	=> 'product_specifications_tab_content'
		);
	}

	if(get_post_meta($product_id, '_cmb_electrical_requirements', true)){
		$tabs['electrical_requirements_tab'] = array(
			'title' 	=> __( 'Electrical Requirements', 'Divi' ),
			'priority' 	=> 54,
			'callback' 	=> 'electrical_requirements_tab_content'
		);
	}

	if(get_post_meta($product_id, '_cmb_product_video_url', true)){
		$tabs['product_videos_tab'] = array(
			'title' 	=> __( 'Videos', 'Divi' ),
			'priority' 	=> 55,
			'callback' 	=> 'product_videos_tab_content'
		);
	}

	if(get_post_meta($product_id, '_cmb_recommended_suppliers', true)){
		$tabs['recommended_suppliers_tab'] = array(
			'title' 	=> __( 'Recommended Suppliers', 'Divi' ),
			'priority' 	=> 56,
			'callback' 	=> 'recommended_suppliers_tab_content'
		);
	}

	if(get_post_meta($product_id, '_cmb_product_manuals', true)){
		$tabs['product_manuals_tab'] = array(
			'title' 	=> __( 'Manuals', 'Divi' ),
			'priority' 	=> 57,
			'callback' 	=> 'product_manuals_tab_content'
		);
	}

    return $tabs;
}

function product_specifications_tab_content() {
	global $post;
	$product_id = $post->ID;
	if(get_post_meta($product_id, '_cmb_product_specifications', true)){
		//echo '<h2>Specifications</h2>';	
		echo get_post_meta($product_id, '_cmb_product_specifications', true);
	}
}

function electrical_requirements_tab_content() {
	global $post;
	$product_id = $post->ID;
	if(get_post_meta($product_id, '_cmb_electrical_requirements', true)){
		//echo '<h2>Specifications</h2>';	
		echo get_post_meta($product_id, '_cmb_electrical_requirements', true);
	}
}

function product_videos_tab_content() {
	global $post;
	$product_id = $post->ID;
	if(get_post_meta($product_id, '_cmb_product_video_url', true)){
		echo get_post_meta($product_id, '_cmb_product_video_url', true);
	}
}

function recommended_suppliers_tab_content() {
	global $post;
	$product_id = $post->ID;
	if(get_post_meta($product_id, '_cmb_recommended_suppliers', true)){
		echo get_post_meta($product_id, '_cmb_recommended_suppliers', true);
	}
}

function product_manuals_tab_content() {
	global $post;
	$product_id = $post->ID;
	if(get_post_meta($product_id, '_cmb_product_manuals', true)){
		echo get_post_meta($product_id, '_cmb_product_manuals', true);
	}
}

add_filter( 'woocommerce_breadcrumb_defaults', 'lis_change_breadcrumb_delimiter' );
function lis_change_breadcrumb_delimiter( $defaults ) {
	// Change the breadcrumb delimeter from '/' to '>'
	$defaults['delimiter'] = ' &gt; ';
	return $defaults;
}

// Removes breadcrumb from it's original position
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
add_action('woocommerce_custom_tabs', 'woocommerce_output_product_data_tabs', 10);

// Removes related products
remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);


/**
 * Register new endpoint to use inside My Account page.
 *
 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
 */
function service_report_custom_endpoint() {
    add_rewrite_endpoint( 'service-report', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'service_report_custom_endpoint' );

/**
 * Add new query var.
 *
 * @param array $vars
 * @return array
 */
function lis_custom_query_vars( $vars ) {
    $vars[] = 'service-report';

    return $vars;
}
add_filter( 'query_vars', 'lis_custom_query_vars', 0 );

/**
 * Insert the new endpoint into the My Account menu.
 *
 * @param array $items
 * @return array
 */
function lis_custom_my_account_menu_items( $items ) {
    // Remove the logout menu item.
    $logout = $items['customer-logout'];
    unset( $items['customer-logout'] );

    // Insert your custom endpoint.
    $items['service-report'] = __( 'Service Report', 'woocommerce' );

    // Insert back the logout item.
    $items['customer-logout'] = $logout;

    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'lis_custom_my_account_menu_items' );

/**
 * Endpoint HTML content.
 */
function service_report_endpoint_content() {
	$user_id = get_current_user_id();
	$args = array(
				'post_type' => 'pdfs',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'order' => 'DESC',
				'orderby' => 'date',
				'meta_query' => array(
					array(
						'key'     => '_cmb_lis_user_access',
						'value'   => ':"'.$user_id.'";',
						'compare' => 'LIKE',
					),
				),
			);
	$pdf_post = new WP_Query( $args );

	if( $pdf_post->have_posts() ) :
	echo '<style type="text/css">
			.pdf-doc { padding: 10px; }
			.pdf-doc:nth-child(odd) { background: #eeeeee; }
			.pdf-doc span { float: right; }
		</style>';
	while( $pdf_post->have_posts() ) : $pdf_post->the_post();
		global $post;
		if(get_post_meta($post->ID, '_cmb_lis_pdf', true)) {
			$pdf_file = get_post_meta($post->ID, '_cmb_lis_pdf', true);
			echo '<div class="pdf-doc">';
			if($pdf_file) {
				//$file_name_arr = explode(".", end(explode("/", $pdf_file)));
				//$file_name = ucwords(str_replace("-", " ", $file_name_arr[0]));
				$file_name = get_the_title();
				echo '<a href="'.$pdf_file.'" target="_blank">'.$file_name.' <img src="'.get_stylesheet_directory_uri().'/images/bullet-pdf.gif" alt="" /></a>';
				if(get_post_meta($post->ID, '_cmb_pdf_upload_date', true)) {
					echo ' &nbsp;&nbsp;&nbsp;<span>File Uploaded: '.get_post_meta($post->ID, '_cmb_pdf_upload_date', true).'</span>';
				}
			}
			echo '</div>';
		}
	endwhile;
	wp_reset_postdata();

	endif;
}
add_action( 'woocommerce_account_service-report_endpoint', 'service_report_endpoint_content' );

/*
 * Change endpoint title.
 * To change the page title for the endpoint:
 *
 * @param string $title
 * @return string
 */
function service_report_endpoint_title( $title ) {
    global $wp_query;

    $is_endpoint = isset( $wp_query->query_vars['service-report'] );

    if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
        // New page title.
        $title = __( 'Service Report', 'woocommerce' );

        remove_filter( 'the_title', 'service_report_endpoint_title' );
    }

    return $title;
}

add_filter( 'the_title', 'service_report_endpoint_title' );

function lis_service_form($content=null) {
	ob_start();
	if( isset($_POST['servicing_submit']) ) {
		$num_of_rows = $_POST['num_of_rows'];

		$companyname = $_POST['companyname'];
		$name = $_POST['pros_name'];
		$streetaddress = $_POST['streetaddress'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zipcode = $_POST['zipcode'];
		$phone = $_POST['phone'];
		$fax = $_POST['fax'];
		$email = $_POST['email'];
		
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$message = "Name: $name<br>";
		$message .= "Company Name: $companyname<br>";
		$message .= "Email: $email<br>";
		$message .= "Phone: $phone<br>";
		$message .= "Fax: $fax<br>";
		$message .= "Street Address: $streetaddress<br>";
		$message .= "City: $city<br>";
		$message .= "Zip Code: $zipcode<br>";
		$message .= "State: $state<br>";
		$message .= "<br>";
		$message .= "<strong>Instrument Information</strong> <br>";
		$message .= "<br>";
		for($i=1; $i<=$num_of_rows; $i++) {
			if( $_POST['instrumentmake'.$i] != '' ) {
				$message .= "Instrument Make: ".$_POST['instrumentmake'.$i]."<br>";
			}
			if( $_POST['instrumentmodel'.$i] != '' ) {
				$message .= "Instrument Model: ".$_POST['instrumentmodel'.$i]."<br>";
			}
			if( $_POST['instrumentserial'.$i] != '' ) {
				$message .= "Instrument Serial: ".$_POST['instrumentserial'.$i]."<br>";
			}
			if( $_POST['reasonforservice'.$i] != '' ) {
				$message .= "Reason for Service: ".$_POST['reasonforservice'.$i]."<br>";
			}
			
			if( ($_POST['instrumentmake'.$i] != '') || ($_POST['instrumentmodel'.$i] != '') || ($_POST['instrumentserial'.$i] != '') || ($_POST['reasonforservice'.$i] != '') ) {
				$message .= "<hr>";
			}
		}
		$message .= "<br>";
		$message .= "~Thanks";
	
		//$user_email = 'saddam987020@gmail.com';
		$admin_email = get_option( 'admin_email' );
		wp_mail( $admin_email, 'Service Request', $message, $headers );
		
		echo '<p style="text-align: center; color: green; margin-bottom: 5px;">Your service request has been sent.</p>';
	}
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery(".add-more-instruments").on( "click", function() {
				var row_num = jQuery('.instrument_wrap li').length;
				//alert(row_num);
				row_num++;
				jQuery('.instrument_wrap').append('<li><label><input name="instrumentmake'+row_num+'" placeholder="Instrument Make" type="text"></label><label><input name="instrumentmodel'+row_num+'" placeholder="Instrument Model" type="text"></label><label><input name="instrumentserial'+row_num+'" placeholder="Instrument Serial #" type="text"></label><label><input name="reasonforservice'+row_num+'" placeholder="Reason for Service" type="text"></label></li>');
				jQuery("#num_of_rows").val(row_num);
			});

			jQuery(".servicing-form").submit(function() {
				if( jQuery("input[name=companyname]").val() == '' ) {
					alert("Please enter company name");
					return false;
				} else if( jQuery("input[name=pros_name]").val() == '' ) {
					alert("Please enter name");
					return false;
				} else if( jQuery("input[name=streetaddress]").val() == '' ) {
					alert("Please enter street address");
					return false;
				} else if( jQuery("input[name=city]").val() == '' ) {
					alert("Please enter city");
					return false;
				} else if( jQuery("input[name=state]").val() == '' ) {
					alert("Please enter state");
					return false;
				} else if( jQuery("input[name=zipcode]").val() == '' ) {
					alert("Please enter zip code");
					return false;
				} else if( jQuery("input[name=phone]").val() == '' ) {
					alert("Please enter phone");
					return false;
				} /*else if( jQuery("input[name=fax]").val() == '' ) {
					alert("Please enter fax");
					return false;
				}*/ else if( jQuery("input[name=email]").val() == '' ) {
					alert("Please enter email");
					return false;
				} else if( jQuery("#captcha").val() != jQuery("#captchaTxt").val() ) {
					alert("Captcha doesn't match");
					return false;
				}
			});
			
			jQuery('<div style="text-align:right; margin-top:20px;"><div><input type="text" id="captchaTxt" value="'+makeCaptcha()+'" disabled="disabled" style="background: transparent; border: none; font-family: arial; font-size: 20px; padding-right: 10px; text-align: right;" /><input type="text" id="captcha" autocomplete="off" placeholder="Enter Captcha" style="padding: 8px 5px; vertical-align: bottom; max-width: 150px;" /></div></div>').insertBefore(jQuery("#num_of_rows"));
			function makeCaptcha() {
				var text = "";
				var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
				for( var i=0; i < 5; i++ ) {
					text += possible.charAt(Math.floor(Math.random() * possible.length));
				}
				//return text.toUpperCase();
				return text;
			}
		} );
    </script>
    <div class="service_request">
        <form action="<?php echo home_url('/lis-service/'); ?>" method="post" class="servicing-form">
            <div class="requst_box_one">
                <h3>Service Request Form</h3>
                <h6>Request servicing for your laboratory instruments.</h6>
                <input name="companyname" placeholder="Company Name*" type="text"> 
                <input name="pros_name" placeholder="Name*" type="text"> 
                <input name="streetaddress" placeholder="Street Address*" type="text">
                <input name="city" placeholder="City*" type="text">
                <input name="state" placeholder="State*" type="text">
                <input name="zipcode" placeholder="Zip Code*" type="text">
                <input name="phone" placeholder="Phone*" type="text">
                <input name="fax" placeholder="Fax" type="text">
                <input name="email" placeholder="Email*" type="email">
            </div><!--requst_box_one-->
            
            <div class="instrument clearfix">
                <h3>Instrument Information (Required)</h3>
                <h6>Please fill out the below for the laboratory instruments that need servicing.</h6>
                
                <ul class="instrument_wrap">
                    <li><label><input name="instrumentmake1" placeholder="Instrument Make" type="text"></label><label><input name="instrumentmodel1" placeholder="Instrument Model" type="text"></label><label><input name="instrumentserial1" placeholder="Instrument Serial #" type="text"></label><label><input name="reasonforservice1" placeholder="Reason for Service" type="text"></label></li>
                    
                    <li><label><input name="instrumentmake2" placeholder="Instrument Make" type="text"></label><label><input name="instrumentmodel2" placeholder="Instrument Model" type="text"></label><label><input name="instrumentserial2" placeholder="Instrument Serial #" type="text"></label><label><input name="reasonforservice2" placeholder="Reason for Service" type="text"></label></li>
                    
                    <li><label><input name="instrumentmake3" placeholder="Instrument Make" type="text"></label><label><input name="instrumentmodel3" placeholder="Instrument Model" type="text"></label><label><input name="instrumentserial3" placeholder="Instrument Serial #" type="text"></label><label><input name="reasonforservice3" placeholder="Reason for Service" type="text"></label></li>
                    
                    <li><label><input name="instrumentmake4" placeholder="Instrument Make" type="text"></label><label><input name="instrumentmodel4" placeholder="Instrument Model" type="text"></label><label><input name="instrumentserial4" placeholder="Instrument Serial #" type="text"></label><label><input name="reasonforservice4" placeholder="Reason for Service" type="text"></label></li>
                </ul>
                <a href="javascript: void(0);" class="add-more-instruments">Add More Instruments</a>
            </div><!--instrument-->
            <input type="hidden" name="num_of_rows" id="num_of_rows" value="4" />
            <input name="servicing_submit" value="REQUEST SERVICING" class="sub_mit" type="submit">
        </form>
    </div><!--service_request-->
<?php
	$content = ob_get_clean();
	return $content;
}
add_shortcode('service_form', 'lis_service_form');

add_filter( 'wp_mail_from_name', function( $name ) {
	return 'Laboratory Instrument Specialists';
});

add_action( 'register_form', 'lis_register_form' );
function lis_register_form() {
    $first_name = ( ! empty( $_POST['first_name'] ) ) ? trim( $_POST['first_name'] ) : '';
    $phone = ( ! empty( $_POST['phone'] ) ) ? trim( $_POST['phone'] ) : '';
    $company = ( ! empty( $_POST['company'] ) ) ? trim( $_POST['company'] ) : '';
    $business_address = ( ! empty( $_POST['business_address'] ) ) ? trim( $_POST['business_address'] ) : '';
    $return_shipping_address = ( ! empty( $_POST['return_shipping_address'] ) ) ? trim( $_POST['return_shipping_address'] ) : '';
?>
    <p>
        <label for="first_name"><?php _e( 'Name', 'Divi' ) ?><br />
            <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr( wp_unslash( $first_name ) ); ?>" size="25" /></label>
    </p>
    <p>
        <label for="phone"><?php _e( 'Phone', 'Divi' ) ?><br />
            <input type="text" name="phone" id="phone" class="input" value="<?php echo esc_attr( wp_unslash( $phone ) ); ?>" size="25" /></label>
    </p>
    <p>
        <label for="company"><?php _e( 'Company', 'Divi' ) ?><br />
            <input type="text" name="company" id="company" class="input" value="<?php echo esc_attr( wp_unslash( $company ) ); ?>" size="25" /></label>
    </p>
    <p>
        <label for="business_address"><?php _e( 'Business Address', 'Divi' ) ?><br />
            <input type="text" name="business_address" id="business_address" class="input" value="<?php echo esc_attr( wp_unslash( $business_address ) ); ?>" size="25" /></label>
    </p>
    <p>
        <label for="return_shipping_address"><?php _e( 'Return Shipping Address', 'Divi' ) ?><br />
            <input type="text" name="return_shipping_address" id="return_shipping_address" class="input" value="<?php echo esc_attr( wp_unslash( $return_shipping_address ) ); ?>" size="25" /></label>
    </p>
<?php
}

add_filter( 'registration_errors', 'lis_registration_errors', 10, 3 );
function lis_registration_errors( $errors, $sanitized_user_login, $user_email ) {
	
	if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
		$errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a name.', 'Divi' ) );
	}
	if ( empty( $_POST['phone'] ) || ! empty( $_POST['phone'] ) && trim( $_POST['phone'] ) == '' ) {
		$errors->add( 'phone_error', __( '<strong>ERROR</strong>: You must include a phone number.', 'Divi' ) );
	}
	if ( empty( $_POST['company'] ) || ! empty( $_POST['company'] ) && trim( $_POST['company'] ) == '' ) {
		$errors->add( 'company_error', __( '<strong>ERROR</strong>: You must include a company.', 'Divi' ) );
	}
	if ( empty( $_POST['business_address'] ) || ! empty( $_POST['business_address'] ) && trim( $_POST['business_address'] ) == '' ) {
		$errors->add( 'business_address_error', __( '<strong>ERROR</strong>: You must include a business address.', 'Divi' ) );
	}

	return $errors;
}

add_action( 'user_register', 'lis_user_register' );
function lis_user_register( $user_id ) {
	if ( ! empty( $_POST['first_name'] ) ) {
		update_user_meta( $user_id, 'first_name', trim( $_POST['first_name'] ) );
	}
	if ( ! empty( $_POST['phone'] ) ) {
		update_user_meta( $user_id, 'user_phone', trim( $_POST['phone'] ) );
	}
	if ( ! empty( $_POST['company'] ) ) {
		update_user_meta( $user_id, 'user_company', trim( $_POST['company'] ) );
	}
	if ( ! empty( $_POST['business_address'] ) ) {
		update_user_meta( $user_id, 'user_business_address', trim( $_POST['business_address'] ) );
	}
	if ( ! empty( $_POST['return_shipping_address'] ) ) {
		update_user_meta( $user_id, 'user_return_shipping_address', trim( $_POST['return_shipping_address'] ) );
	}
}

function show_custom_product_options() {
	global $post;
	if(get_post_meta($post->ID, '_cmb_product_video_url', true)) {
		echo '<div style="margin-top: 20px; padding-bottom: 10px;">';
		echo '<h4>Videos</h4>';
		echo get_post_meta($post->ID, '_cmb_product_video_url', true);
		echo '</div>';
	}
	if(get_post_meta($post->ID, '_cmb_recommended_suppliers', true)) {
		echo '<div style="margin-top: 20px; padding-bottom: 10px;">';
		echo '<h4>Recommended Suppliers</h4>';
		echo get_post_meta($post->ID, '_cmb_recommended_suppliers', true);
		echo '</div>';
	}
	if(get_post_meta($post->ID, '_cmb_product_manuals', true)) {
		echo '<div style="margin-top: 20px; padding-bottom: 10px;">';
		echo '<h4>Manuals</h4>';
		echo get_post_meta($post->ID, '_cmb_product_manuals', true);
		echo '</div>';
	}
}
//add_action('woocommerce_single_product_summary', 'show_custom_product_options', 25);
?>