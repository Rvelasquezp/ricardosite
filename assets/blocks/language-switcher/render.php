<?php
$current_lang = isset($_COOKIE['site_language'])
    ? sanitize_text_field($_COOKIE['site_language'])
    : 'fr';

$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

// Para agregar un idioma nuevo: añade una entrada aquí
$languages = [
    'fr' => ['label' => 'FR', 'aria' => 'Français'],
    'en' => ['label' => 'EN', 'aria' => 'English'],
    'es' => ['label' => 'ES', 'aria' => 'Español'],
];

// Solo los idiomas que NO son el actual
$other_langs = array_filter(
    $languages,
    fn($code) => $code !== $current_lang,
    ARRAY_FILTER_USE_KEY
);
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'language-switcher']); ?>>
    <?php
    $codes = array_keys($other_langs);
    $last  = end($codes);
    foreach ($other_langs as $code => $info) :
        $url = add_query_arg('set_lang', $code, $current_url);
    ?>
        <a href="<?php echo esc_url($url); ?>"
           class="lang-btn"
           hreflang="<?php echo esc_attr($code); ?>"
           aria-label="<?php echo esc_attr($info['aria']); ?>"
        ><?php echo esc_html($info['label']); ?></a>
        <?php if ($code !== $last) : ?>
            <span class="lang-separator" aria-hidden="true">|</span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
