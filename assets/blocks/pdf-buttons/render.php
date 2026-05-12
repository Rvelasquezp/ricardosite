<?php
$lang = isset($_COOKIE['site_language']) ? sanitize_text_field($_COOKIE['site_language']) : 'fr';

$field_map = [
    'fr' => 'file_fr',
    'en' => 'file_en',
    'es' => 'file_es',
];

$field_key = $field_map[$lang] ?? 'file_fr';
$file      = get_field($field_key, 'option');

// ACF File field returns an array with url, filename, etc.
$file_url  = is_array($file) ? ($file['url'] ?? '') : (is_string($file) ? $file : '');
$file_name = is_array($file) ? ($file['filename'] ?? 'document.pdf') : 'document.pdf';

$labels = [
    'fr' => ['download' => 'Télécharger le PDF', 'view' => 'Voir le PDF'],
    'en' => ['download' => 'Download PDF',        'view' => 'View PDF'],
    'es' => ['download' => 'Descargar PDF',       'view' => 'Ver PDF'],
];
$label = $labels[$lang] ?? $labels['fr'];
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'pdf-buttons']); ?>>
    <?php if ($file_url) : ?>
        <a class="pdf-btn pdf-btn--download"
           href="<?php echo esc_url($file_url); ?>"
           target="_blank"
           rel="noopener">
            <span><?php echo esc_html($label['download']); ?></span>
        </a>
        <button class="pdf-btn pdf-btn--view"
                data-pdf="<?php echo esc_url($file_url); ?>"
                aria-haspopup="dialog">
            <span><?php echo esc_html($label['view']); ?></span>
        </button>
    <?php else : ?>
        <p class="pdf-buttons__empty">
            <?php echo $lang === 'en' ? 'No PDF uploaded yet.' : ($lang === 'es' ? 'No se ha subido un PDF aún.' : 'Aucun PDF téléversé.'); ?>
        </p>
    <?php endif; ?>
</div>
