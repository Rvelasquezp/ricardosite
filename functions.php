<?php
require_once get_template_directory() . '/theme-includes.php';

if (!function_exists('theme_setup')) {
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which runs
	 * before the init hook.
	 */
	function theme_setup()
	{
		// Add support for block styles.
		add_theme_support('wp-block-styles');

		// Enqueue editor styles.
		add_editor_style(['assets/css/editor-style.css', 'build/style-index.css', 'build/index.css']);
	}
}

add_action('after_setup_theme', 'theme_setup');

function add_customize_button_to_appearance_menu()
{
	add_submenu_page(
		'themes.php',        // Parent menu slug (for Appearance)
		'Customize',         // Page title
		'Customize',         // Menu title
		'edit_theme_options', // Capability required
		'customize.php'      // Menu slug
	);
}

add_action('admin_menu', 'add_customize_button_to_appearance_menu');

function utopian_theme_scripts()
{
	$theme_version = '1.0.0';
	wp_enqueue_style('style', get_stylesheet_directory_uri() . '/build/index.css', [], $theme_version);
	wp_enqueue_style('theme-style', get_stylesheet_directory_uri() . '/build/style-index.css', [], $theme_version);
	wp_enqueue_script('utopian', get_stylesheet_directory_uri() . '/build/index.js', [], $theme_version, true);
}

add_action('wp_enqueue_scripts', 'utopian_theme_scripts');

function be_gutenberg_scripts()
{
	wp_enqueue_script(
		'utopian-editor',
		get_stylesheet_directory_uri() . '/build/js/editor.js',
		'1',
		array('wp-blocks', 'wp-dom'),
		true
	);
}

add_action('enqueue_block_editor_assets', 'be_gutenberg_scripts');

function be_reusable_blocks_admin_menu()
{
	add_menu_page('Reusable Blocks', 'Reusable Blocks', 'edit_posts', 'edit.php?post_type=wp_block', '', 'dashicons-editor-table', 22);
}
add_action('admin_menu', 'be_reusable_blocks_admin_menu');

add_action('wp_head', function () {
	remove_action('wp_head', '_block_template_viewport_meta_tag', 0);
}, PHP_INT_MIN);

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function utopian_theme_pingback_header()
{
	echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';

	if (is_singular() && pings_open()) {
		echo '<link rel="pingback" href="', esc_url(get_bloginfo('pingback_url')), '">';
	}
}

add_action('wp_head', 'utopian_theme_pingback_header');

class JSXBlock
{
	public $name;
	public $location;
	public $renderCallback;
	public $data;
	public $dataIsFunction;
	public $dependencies;

	function __construct($name, $renderCallback = null, $data = null, $dataIsFunction = false, $dependencies = ['wp-element'])
	{
		$this->name = $name;
		$this->location = "/build/blocks/{$name}/";
		$this->renderCallback = $renderCallback;
		$this->data = $data;
		$this->dataIsFunction = $dataIsFunction;
		$this->dependencies = $dependencies;

		$block_json_path = __DIR__ . $this->location . 'block.json';

		if (file_exists($block_json_path)) {
			$block_json = json_decode(file_get_contents($block_json_path), true);

			if ($block_json && isset($block_json['acf'])) {
				add_action('acf/init', [$this, 'onInit']);
				return;
			}
		}

		// Default to init if no ACF key or file missing
		add_action('init', [$this, 'onInit']);
	}

	function onInit()
	{
		$block_dir = __DIR__ . $this->location;
		$block_json_path = $block_dir . 'block.json';

		if (!file_exists($block_json_path)) {
			error_log("block.json not found for block: {$this->name}");
			return;
		}

		$block_json = json_decode(file_get_contents($block_json_path), true);

		if (!$block_json) {
			error_log("Invalid block.json for block: {$this->name}");
			return;
		}

		// Flatten the ACF-specific settings to top-level
		if (isset($block_json['acf'])) {

			// Check function exists.
			if (function_exists('acf_register_block_type')) {

				// Register a testimonial block.
				acf_register_block_type([
					'name' => $this->name,
					'title' => $block_json['title'],
					"supports" => [
						'jsx' => true,
					],
					'render_template' => "build/blocks/" . $this->name . "/" . $block_json['acf']['renderTemplate'],
				]);

				return;
			}
		}

		// Add optional renderCallback
		if ($this->renderCallback && is_callable($this->renderCallback)) {
			$block_json['render_callback'] = $this->renderCallback;
		}

		// Now register with full array
		register_block_type_from_metadata($block_dir, $block_json);
	}
}

new JSXBlock('faq', true);

// change icon login page
function custom_login_logo_svg() {
	?>
<style type="text/css">
#login h1 a {
    background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/assets/images/pixel.svg');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    width: 100%;
    /* height: 120px; */
}
</style>
<?php
}
add_action('login_enqueue_scripts', 'custom_login_logo_svg');
add_filter('login_headerurl', function () {
	return home_url();
});
// change icon login page



add_filter('block_categories_all', function ($categories) {

	// Adding a new category.
	$categories[] = array(
		'slug'  => 'utopian',
		'title' => 'Utopian'
	);

	return $categories;
});

add_filter('wpcf7_autop_or_not', '__return_false');

add_action('acf/init', 'my_acf_blocks_init');
function my_acf_blocks_init()
{
	if (function_exists('acf_add_options_page')) {
		acf_add_options_page();
	}

	// 	Add Google API Key
	// 	acf_update_setting('google_api_key', 'xxx');
}

// Extra functions you might need
/*
// Our custom post type function
function create_posttype()
{

	register_post_type(
		'equipe',
		// CPT Options
		array(
			'labels' => array(
				'name' => __('Équipe'),
				'singular_name' => __("Membre de l'équipe")
			),
			'menu_icon' => 'dashicons-businessman'
			'supports' => array('title', 'custom-fields', 'thumbnail','excerpt', 'page-attributes'),			
			'rewrite' => array('slug' => 'equipe', 'with_front' => true, 'pages' => true, 'feeds' => true),
			'menu_position' => 6,
			'taxonomies' => array(''),
			'capability_type' => 'page',
			'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
			'can_export' => true,
			'hierarchical' => false,					
			'public' => false,
			'show_ui' => true,		
			'show_in_menu' => true,		
			'has_archive' => true,
			'exclude_from_search' => false,
			'publicly_queryable' => true,						
			'show_in_rest' => true,	
			
		)
	);

	register_taxonomy(
		'categorie-equipe',
		'equipe',
		array(
			'hierarchical' => true,
			'label' => 'Catégorie équipe',
			'query_var' => true,
			'show_in_rest' => true,
			'rewrite' => array(
				'slug' => 'categorie-equipe',
				'with_front' => false
			),
			'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
		)
	);

	add_filter('woocommerce_show_page_title', '__return_true', 1);
	add_filter('woocommerce_single_product_summary', 'woocommerce_template_single_title', 6);
}
// Hooking up our function to theme setup
add_action('init', 'create_posttype');*/


// hide admin bar
// add_filter('show_admin_bar', '__return_false');
// hide admin bar