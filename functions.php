<?php
require_once get_template_directory() . '/theme-includes.php';

// ── i18n: cambiar idioma según cookie ──
add_filter('locale', 'theme_custom_locale');
function theme_custom_locale($locale) {
	if (isset($_COOKIE['site_language'])) {
		$lang = sanitize_text_field($_COOKIE['site_language']);
		if ($lang === 'fr') return 'fr_FR';
		if ($lang === 'en') return 'en_US';
		if ($lang === 'es') return 'es_ES';
	}
	return $locale;
}

// ── i18n: mapa de páginas  'ruta-en' => 'ruta-fr' ──
// Usa el path completo para páginas hijas (ej: 'en/about' => 'a-propos')
function theme_page_language_map() {
	return [
		'en'         => '',           // /en        ↔  / (front page EN)
		'es'        => '',           // /es          ↔  / (front page ES)
		// 'en/about'  => 'a-propos',
		// 'es/about'  => 'a-propos',
	];
}

// Idiomas soportados — agregar aquí cuando se cree la página y contenido
function theme_supported_languages() {
	return ['fr', 'en', 'es'];
}

// ── i18n: detección automática del idioma del browser (solo primer visita) ──
add_action('template_redirect', 'theme_detect_browser_language', 5);
function theme_detect_browser_language() {
	if (isset($_COOKIE['site_language'])) return;
	if (isset($_GET['set_lang'])) return;

	$accept = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
	$lang   = 'fr'; // francés por defecto

	// Parsear Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7
	preg_match_all('/([a-z]{2})(?:-[a-z]{2})?(?:;q=([0-9.]+))?/i', $accept, $matches, PREG_SET_ORDER);
	$scores = [];
	foreach ($matches as $m) {
		$code   = strtolower($m[1]);
		$q      = isset($m[2]) && $m[2] !== '' ? (float) $m[2] : 1.0;
		if (!isset($scores[$code]) || $scores[$code] < $q) $scores[$code] = $q;
	}

	$fr_q = $scores['fr'] ?? 0;
	$en_q = $scores['en'] ?? 0;

	// Solo detectar inglés automáticamente — español se activa manualmente
	// cuando el contenido en ES esté listo
	if ($en_q > $fr_q) $lang = 'en';

	// Verificar que la página del idioma detectado existe antes de usarla
	if ($lang !== 'fr') {
		$map    = theme_page_language_map();
		$exists = false;
		foreach ($map as $map_path => $map_fr_base) {
			if ($map_path === $lang || strpos($map_path, $lang . '/') === 0) {
				if (get_page_by_path($map_path)) $exists = true;
				break;
			}
		}
		if (!$exists) $lang = 'fr';
	}

	setcookie('site_language', $lang, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
	$_COOKIE['site_language'] = $lang;

	// Si el browser prefiere otro idioma y estamos en la homepage francesa → redirigir
	if ($lang !== 'fr' && is_front_page()) {
		foreach (theme_page_language_map() as $other_path => $fr_path) {
			if ($fr_path === '' && strpos($other_path, $lang) === 0) {
				$other_page = get_page_by_path($other_path);
				if ($other_page) {
					wp_safe_redirect(get_permalink($other_page));
					exit;
				}
				break;
			}
		}
	}
}

// ── i18n: sincronizar cookie cuando se aterriza directo en página de idioma ──
// Ej: visitar /en directamente sin pasar por el switcher actualiza cookie a 'en'
add_action('template_redirect', 'theme_sync_cookie_from_page', 6);
function theme_sync_cookie_from_page() {
	if ( isset( $_GET['set_lang'] ) ) return; // ya lo maneja theme_handle_language_switch
	if ( ! is_singular() || is_front_page() ) return; // front page = siempre fr, no sincronizar

	$post = get_queried_object();
	if ( ! ( $post instanceof WP_Post ) ) return;

	$current_path = get_page_uri( $post );
	$langs        = theme_supported_languages();

	foreach ( theme_page_language_map() as $map_path => $fr_base ) {
		if ( $map_path !== $current_path ) continue;

		$lang = explode( '/', $map_path )[0];
		if ( ! in_array( $lang, $langs, true ) ) break;

		// Actualizar cookie para esta request Y para el browser
		$_COOKIE['site_language'] = $lang;
		setcookie( 'site_language', $lang, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		return;
	}
}

// ── i18n: interceptar ?set_lang=XX → cookie → redirigir a la página equivalente ──
add_action('template_redirect', 'theme_handle_language_switch', 10);
function theme_handle_language_switch() {
	if (!isset($_GET['set_lang'])) return;

	$lang = sanitize_text_field($_GET['set_lang']);
	if (!in_array($lang, theme_supported_languages(), true)) return;

	setcookie('site_language', $lang, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

	$map      = theme_page_language_map();
	$current  = get_queried_object();
	$redirect = remove_query_arg('set_lang');

	if (!($current instanceof WP_Post)) {
		wp_safe_redirect($redirect);
		exit;
	}

	$current_path = get_page_uri($current);

	// Paso 1: encontrar el "base FR" de la página actual
	$fr_base = null;
	if (is_front_page()) {
		$fr_base = '';
	} else {
		foreach ($map as $map_path => $map_fr_base) {
			if ($map_path === $current_path || $map_fr_base === $current_path) {
				$fr_base = $map_fr_base;
				break;
			}
		}
	}

	if ($fr_base === null) {
		wp_safe_redirect($redirect);
		exit;
	}

	// Paso 2: si el target es francés → ir a la página base FR
	if ($lang === 'fr') {
		$redirect = ($fr_base === '') ? home_url('/') : get_permalink(get_page_by_path($fr_base));
		wp_safe_redirect($redirect ?: home_url('/'));
		exit;
	}

	// Paso 3: buscar la página del idioma target en el mapa
	foreach ($map as $map_path => $map_fr_base) {
		if ($map_fr_base !== $fr_base) continue;
		// La clave del mapa empieza con el código de idioma: 'en', 'es', 'en/about', 'es/about'
		if ($map_path === $lang || strpos($map_path, $lang . '/') === 0) {
			$target = get_page_by_path($map_path);
			$redirect = $target ? get_permalink($target) : home_url('/');
			break;
		}
	}

	wp_safe_redirect($redirect ?: home_url('/'));
	exit;
}

// ── i18n: hreflang tags para SEO (genérico para N idiomas) ──
add_action('wp_head', 'theme_hreflang_tags');
function theme_hreflang_tags() {
	$map     = theme_page_language_map();
	$current = get_queried_object();
	$urls    = []; // [ 'fr' => url, 'en' => url, 'es' => url ]

	if (is_front_page()) {
		$urls['fr'] = home_url('/');
		foreach ($map as $map_path => $map_fr_base) {
			if ($map_fr_base !== '') continue;
			$lang_code  = explode('/', $map_path)[0];
			$page       = get_page_by_path($map_path);
			if ($page) $urls[$lang_code] = get_permalink($page);
		}
	} elseif ($current instanceof WP_Post) {
		$current_path = get_page_uri($current);
		$fr_base      = null;

		foreach ($map as $map_path => $map_fr_base) {
			if ($map_path === $current_path) {
				$lang_code        = explode('/', $map_path)[0];
				$urls[$lang_code] = get_permalink($current);
				$fr_base          = $map_fr_base;
				break;
			}
			if ($map_fr_base === $current_path) {
				$urls['fr'] = get_permalink($current);
				$fr_base    = $map_fr_base;
				break;
			}
		}

		if ($fr_base !== null) {
			if (!isset($urls['fr'])) {
				$urls['fr'] = ($fr_base === '') ? home_url('/') : get_permalink(get_page_by_path($fr_base));
			}
			foreach ($map as $map_path => $map_fr_base) {
				if ($map_fr_base !== $fr_base || $map_path === $current_path) continue;
				$lang_code = explode('/', $map_path)[0];
				$page      = get_page_by_path($map_path);
				if ($page) $urls[$lang_code] = get_permalink($page);
			}
		}
	}

	if (empty($urls)) return;

	foreach ($urls as $lang_code => $url) {
		printf('<link rel="alternate" hreflang="%s" href="%s">' . "\n\t", esc_attr($lang_code), esc_url($url));
	}
	printf('<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url($urls['fr'] ?? home_url('/')));
}

// ── i18n: agregar clase lang-fr / lang-en / lang-es al <body> ──
add_filter('body_class', 'theme_body_language_class');
function theme_body_language_class($classes) {
	$lang      = isset($_COOKIE['site_language']) ? sanitize_text_field($_COOKIE['site_language']) : 'fr';
	$classes[] = 'lang-' . $lang;
	return $classes;
}

// ── i18n: intercambiar formulario Gravity Forms según idioma ──
add_filter('render_block', 'theme_swap_gravity_form', 10, 2);
function theme_swap_gravity_form($content, $block) {
	if ($block['blockName'] !== 'gravityforms/form') return $content;

	$forms = [
		'fr' => 1,  // Contact fr
		'en' => 2,  // Contact en
		'es' => 3,  // Contact es
	];

	$current_id = (int)($block['attrs']['formId'] ?? 0);

	// Solo actuar si el bloque usa uno de nuestros formularios
	if (!in_array($current_id, $forms, true)) return $content;

	$lang      = isset($_COOKIE['site_language']) ? sanitize_text_field($_COOKIE['site_language']) : 'fr';
	$target_id = $forms[$lang] ?? $forms['fr'];

	// Ya está mostrando el formulario correcto
	if ($current_id === $target_id) return $content;

	return render_block([
		'blockName'   => 'gravityforms/form',
		'attrs'       => array_merge($block['attrs'], ['formId' => (string)$target_id]),
		'innerBlocks' => [],
	]);
}

// ── i18n: intercambiar menú de navegación según idioma (un solo header) ──
add_filter('render_block', 'theme_swap_navigation_menu', 10, 2);
function theme_swap_navigation_menu($content, $block) {
	if ($block['blockName'] !== 'core/navigation') return $content;

	$menus = [
		'fr' => 4,    // Menu FR  — ID del menú en Site Editor
		'en' => 252,  // Menu EN  — ID del menú en Site Editor
		'es' => 253,  // Menu ES  — ID del menú en Site Editor
	];

	$current_ref = $block['attrs']['ref'] ?? null;

	// Solo actuar si el bloque usa uno de nuestros menús
	if (!in_array($current_ref, $menus, true)) return $content;

	$lang       = isset($_COOKIE['site_language']) ? sanitize_text_field($_COOKIE['site_language']) : 'fr';
	$target_id  = $menus[$lang] ?? $menus['fr'];

	// Ya está mostrando el menú correcto
	if ($current_ref === $target_id) return $content;

	return render_block([
		'blockName'   => 'core/navigation',
		'attrs'       => array_merge($block['attrs'], ['ref' => $target_id]),
		'innerBlocks' => [],
	]);
}

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

		// Cargar traducciones del tema desde /languages
		load_theme_textdomain('utopian-theme', get_template_directory() . '/languages');
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

new JSXBlock('accordion', true);
new JSXBlock('hamburger', true);
new JSXBlock('projets', true);
new JSXBlock('language-switcher');
new JSXBlock('pdf-buttons');

// change icon login page
function custom_login_logo_svg() {
	?>
<style type="text/css">
#login h1 a {
    background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/assets/images/ingcloud.svg');
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
// Our custom post type function
function create_posttype()
{

	register_post_type(
		'projets',
		// CPT Options
		array(
			'labels' => array(
				'name' => __('Projets'),
				'singular_name' => __("projet")
			),			
			'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 293.45" fill="#ffffff"><path d="M12.93 267.59h47.76c-1.56-1.62-2.53-3.81-2.53-6.22V8.97C58.16 4.04 62.2 0 67.13 0h375.38c4.93 0 8.97 4.04 8.97 8.97v252.4c0 2.41-.97 4.6-2.53 6.22h50.12c3.56 0 6.8 1.45 9.14 3.79a12.903 12.903 0 0 1 0 18.27c-2.34 2.34-5.58 3.8-9.14 3.8H12.93c-3.56 0-6.8-1.46-9.14-3.8a12.903 12.903 0 0 1 0-18.27c2.34-2.34 5.58-3.79 9.14-3.79zM339 181.33c.67-2.91 3.59-4.72 6.5-4.05 2.91.68 4.72 3.59 4.05 6.51l-10.59 45.34a5.412 5.412 0 0 1-6.5 4.04 5.406 5.406 0 0 1-4.05-6.5L339 181.33zM97.79 82.82c-3.52 0-6.37-2.85-6.37-6.37 0-3.51 2.85-6.37 6.37-6.37h160.46c3.51 0 6.36 2.86 6.36 6.37 0 3.52-2.85 6.37-6.36 6.37H97.79zm0 40.57c-3.52 0-6.37-2.86-6.37-6.37 0-3.52 2.85-6.37 6.37-6.37h148.57c3.51 0 6.36 2.85 6.36 6.37 0 3.51-2.85 6.37-6.36 6.37H97.79zm0 81.13c-3.52 0-6.37-2.85-6.37-6.37s2.85-6.37 6.37-6.37h104.63c3.51 0 6.37 2.85 6.37 6.37s-2.86 6.37-6.37 6.37H97.79zm0-40.57a6.365 6.365 0 1 1 0-12.73h114.97a6.365 6.365 0 1 1 0 12.73H97.79zm227.12-84.76c0-4.31 1.24-8.3 3.36-11.71h37.7c2.12 3.38 3.35 7.4 3.35 11.71 0 12.26-9.94 22.21-22.2 22.21s-22.21-9.95-22.21-22.21zm86.29.84v4.65c0 4.35-3.55 7.91-7.9 7.91h-8.1a49.25 49.25 0 0 1-4.88 12.88l5.4 5.4c3.07 3.08 3.07 8.11 0 11.18l-6.58 6.59c-3.07 3.07-8.1 3.07-11.18 0l-4.92-4.93c-4.1 2.55-8.59 4.51-13.36 5.78v6.72c0 4.35-3.57 7.91-7.91 7.91h-9.3c-4.35 0-7.91-3.56-7.91-7.91v-6.63a49.3 49.3 0 0 1-13.5-5.73l-4.79 4.79c-3.07 3.07-8.11 3.07-11.18 0l-6.58-6.58c-3.07-3.08-3.07-8.11 0-11.19l5.18-5.17a49.215 49.215 0 0 1-5.01-13.11h-7.74c-4.35 0-7.91-3.56-7.91-7.91v-9.3c0-4.22 3.36-7.7 7.53-7.9h26.48c-1.24 3.4-1.91 7.07-1.91 10.9 0 .56.01 1.11.04 1.65-.03.55-.04 1.1-.04 1.65 0 17.57 14.24 31.82 31.81 31.82s31.82-14.25 31.82-31.82c0-.55-.01-1.1-.05-1.65.04-.54.05-1.09.05-1.65 0-3.83-.68-7.5-1.92-10.9h26.83c4.18.2 7.53 3.68 7.53 7.9v4.65zm-90.26 137.36c2.24 1.97 2.47 5.39.51 7.63a5.412 5.412 0 0 1-7.63.51l-18.53-16.23c-2.24-1.97-2.47-5.39-.51-7.63l.53-.53 18.51-16.21c2.24-1.97 5.66-1.74 7.63.51 1.96 2.24 1.73 5.66-.51 7.63l-13.88 12.16 13.88 12.16zm44.45 8.14a5.412 5.412 0 0 1-7.63-.51c-1.96-2.24-1.73-5.66.51-7.63l13.88-12.16-13.88-12.16c-2.24-1.97-2.47-5.39-.51-7.63 1.97-2.25 5.39-2.48 7.63-.51l18.51 16.21.53.53c1.96 2.24 1.73 5.66-.52 7.63l-18.52 16.23zm72.04-188.25H73.49V256.7h363.94V37.28zM117.67 14.45c2.99 0 5.4 2.42 5.4 5.4 0 2.98-2.41 5.4-5.4 5.4-2.98 0-5.4-2.42-5.4-5.4 0-2.98 2.42-5.4 5.4-5.4zm-18.87 0c2.98 0 5.39 2.42 5.39 5.4 0 2.98-2.41 5.4-5.39 5.4-2.99 0-5.41-2.42-5.41-5.4 0-2.98 2.42-5.4 5.41-5.4zm-18.88 0c2.98 0 5.4 2.42 5.4 5.4 0 2.98-2.42 5.4-5.4 5.4-2.99 0-5.4-2.42-5.4-5.4 0-2.98 2.41-5.4 5.4-5.4zm161.35 259.51h29.46c2.76 0 5.02 2.26 5.02 5.02s-2.26 5.02-5.02 5.02h-29.46c-2.76 0-5.02-2.26-5.02-5.02s2.26-5.02 5.02-5.02z"/></svg>'),
			'supports' => array('title', 'custom-fields', 'thumbnail','excerpt', 'page-attributes'),			
			'rewrite' => array('slug' => 'projets', 'with_front' => true, 'pages' => true, 'feeds' => true),
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

	// register_taxonomy(
	// 	'categorie-equipe',
	// 	'equipe',
	// 	array(
	// 		'hierarchical' => true,
	// 		'label' => 'Catégorie équipe',
	// 		'query_var' => true,
	// 		'show_in_rest' => true,
	// 		'rewrite' => array(
	// 			'slug' => 'categorie-equipe',
	// 			'with_front' => false
	// 		),
	// 		'public' => true,
    //         'show_ui' => true,
    //         'show_admin_column' => true,
    //         'show_in_nav_menus' => true,
	// 	)
	// );

	add_filter('woocommerce_show_page_title', '__return_true', 1);
	add_filter('woocommerce_single_product_summary', 'woocommerce_template_single_title', 6);
}
// Hooking up our function to theme setup
add_action('init', 'create_posttype');

add_action('admin_menu', function() {
    remove_menu_page('edit.php'); // Posts
    remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category');
    remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');
});


// popup 

function add_popup()
{
	$custom_logo_id = get_theme_mod('custom_logo');
    $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
	?>
	<div id="popup" class="popup">
		<div class="popup_banner">
			<figure>
				<img src="<?php echo esc_url($logo_url); ?>" alt="">
			</figure>
			<span class="popup-close">
				<svg xmlns="http://www.w3.org/2000/svg" width="29.901" height="29.901" viewBox="0 0 29.901 29.901">
					<g id="Composant_183_3" data-name="Composant 183 – 3" transform="translate(0 0)">
						<g id="Menu" transform="translate(0 0)">
							<path id="Menu-2" data-name="Menu" d="M7.08.05,33.95,26.92l-3.03,3.03L4.049,3.08Z"
								transform="translate(-4.049 -0.05)" fill="#fff" />
						</g>
						<g id="Menu-3" data-name="Menu" transform="translate(0 29.901) rotate(-90)">
							<path id="Menu-4" data-name="Menu" d="M7.08.05,33.95,26.92l-3.03,3.03L4.049,3.08Z"
								transform="translate(-4.049 -0.05)" fill="#fff" />
						</g>
					</g>
				</svg>
			</span>
		</div>
		<div class="popup-content">
			<?php
			$lang      = isset($_COOKIE['site_language']) ? sanitize_text_field($_COOKIE['site_language']) : 'fr';
			$field_map = ['fr' => 'file_fr', 'en' => 'file_en', 'es' => 'file_es'];
			$file      = get_field($field_map[$lang] ?? 'file_fr', 'option');
			$file_url  = is_array($file) ? ($file['url'] ?? '') : (is_string($file) ? $file : '');
			if ($file_url) : ?>
				<object data="<?php echo esc_url($file_url); ?>" type="application/pdf" width="100%" height="100%">
					<p style="color:#fff;text-align:center;padding:2rem">
						<?php echo $lang === 'en' ? 'Unable to display PDF.' : ($lang === 'es' ? 'No se puede mostrar el PDF.' : 'Impossible d\'afficher le PDF.'); ?>
						<a href="<?php echo esc_url($file_url); ?>" style="color:#fff" target="_blank">
							<?php echo $lang === 'en' ? 'Download' : ($lang === 'es' ? 'Descargar' : 'Télécharger'); ?>
						</a>
					</p>
				</object>
			<?php endif; ?>
		</div>
	</div>
<?php
}
add_action('wp_footer', 'add_popup');
// popup 




function ingcloud_meta_description() {
    if ( function_exists('wpseo_frontend_head_init') ) return;

    $lang = isset($_COOKIE['site_language']) ? sanitize_text_field($_COOKIE['site_language']) : 'fr';

    $descriptions = [
        'fr' => 'Développeur WordPress avec plus de 8 ans d\'expérience.',
		'en' => 'WordPress developer with over 8 years of experience.',
		'es' => 'Desarrollador WordPress con más de 8 años de experiencia.',
    ];

    $desc = $descriptions[$lang] ?? $descriptions['fr'];
    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
}
add_action('wp_head', 'ingcloud_meta_description', 1);

// hide admin bar
// add_filter('show_admin_bar', '__return_false');
// hide admin bar