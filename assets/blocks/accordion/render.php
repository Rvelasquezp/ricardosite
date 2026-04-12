<?php
$open = !empty($attributes['open']);
$title = isset($attributes['title']) ? $attributes['title'] : 'Title';
$text  = isset($attributes['text']) ? $attributes['text'] : 'Open';
?>

<div <?php echo get_block_wrapper_attributes([
    'class' => 'accordion ' . ($open ? 'openByDefault' : '')
]); ?>>

    <button class="accordionToggle" type="button">
        <span class="title">
            <?php echo esc_html($title); ?>
        </span>

        <span class="toggle">
            <span class="icon_plus_minus">
            <span class="horizontal"></span>
            <span class="vertical"></span>
        </span>
        </span>
    </button>

    <div class="accordionContent">
        <?php echo $content; ?>
    </div>

</div>