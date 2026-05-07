<?php
/**
 * Block: Projets Slider
 * Renderiza un slider Swiper con CPT "projets" + ACF.
 *
 * @var   array  $block       The block settings and attributes.
 * @var   string $content     The block inner HTML (empty).
 * @var   bool   $is_preview  True during backend preview render.
 */

// ID único por instancia del bloque (permite varios sliders en la misma página).
$block_id = 'projets-' . ( $block['id'] ?? uniqid() );
$align    = ! empty( $block['align'] ) ? 'align' . $block['align'] : '';

// --- Query args ---------------------------------------------------------
$args = array(
	'post_type'      => 'projets',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
);

if ( get_field( 'projets' ) ) {
	$args['post__in'] = get_field( 'projets' );
	$args['orderby']  = 'post__in';
}

$query = new WP_Query( $args );

if ( ! $query->have_posts() ) {
	if ( $is_preview ) {
		echo '<p>Aucun projet trouvé. Crée au moins un projet pour afficher le slider.</p>';
	}
	return;
}

// Helper inline para el ícono de flecha (reutilizable).
$arrow_svg = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M7 17L17 7M17 7H8M17 7V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>

<section id="<?php echo esc_attr( $block_id ); ?>" class="projets-block <?php echo esc_attr( $align ); ?>">

	<!-- ============== MAIN SLIDER (slide grande activo) ============== -->
	<div class="swiper projets-main">
		<div class="swiper-wrapper">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$post_id = get_the_ID();

				$description = get_field( 'description', $post_id );
				$link_projet = get_field( 'link_projet', $post_id );
				$lenguages   = get_field( 'lenguages',   $post_id );
				$thumb_id    = get_post_thumbnail_id( $post_id );
				$thumb_url   = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';
				$thumb_alt   = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : get_the_title();
				?>
				<article class="swiper-slide projets-main__slide">

					<?php if ( $thumb_url ) : ?>
						<div class="projets-main__media">
							<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" loading="lazy" />
						</div>
					<?php endif; ?>

					<div class="projets-main__content">
						<h3 class="projets-main__title"><?php the_title(); ?></h3>

						<?php if ( $description ) : ?>
							<p class="projets-main__desc"><?php echo esc_html( $description ); ?></p>
						<?php endif; ?>

						<?php if ( $link_projet ) : ?>
							<a class="projets-main__cta"
							   href="<?php echo esc_url( $link_projet['url'] ); ?>"
							   target="<?php echo esc_attr( $link_projet['target'] ?: '_self' ); ?>"
							   rel="noopener">
								<span><?php echo esc_html( $link_projet['title'] ?: 'Voir le projet' ); ?></span>
								<?php echo $arrow_svg; // phpcs:ignore ?>
							</a>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $lenguages ) ) : ?>
						<ul class="projets-main__tags">
							<?php foreach ( $lenguages as $lang ) : ?>
								<li class="projets-main__tag"><?php echo esc_html( $lang['title_lenguages'] ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

				</article>
			<?php endwhile; ?>
		</div>
	</div>

	<!-- ============== THUMBS SLIDER (cards pequeñas con mismo hover-reveal) ============== -->
	<div class="projets-thumbs-wrapper">
		<div class="swiper projets-thumbs">
			<div class="swiper-wrapper">
				<?php
				$query->rewind_posts();
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id = get_the_ID(); // ✅ ID del projet actual del loop

					$description = get_field( 'description', $post_id );
					$link_projet = get_field( 'link_projet', $post_id );
					$lenguages   = get_field( 'lenguages',   $post_id );
					$thumb_id    = get_post_thumbnail_id( $post_id );
					$thumb_url   = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium_large' ) : '';
					$thumb_alt   = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : get_the_title();
					?>
					<article class="swiper-slide projets-thumb" tabindex="0">

						<?php if ( $thumb_url ) : ?>
							<div class="projets-thumb__media">
								<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" loading="lazy" />
							</div>
						<?php endif; ?>

						<div class="projets-thumb__content">
							<h4 class="projets-thumb__title"><?php the_title(); ?></h4>

							<?php if ( $description ) : ?>
								<p class="projets-thumb__desc"><?php echo esc_html( $description ); ?></p>
							<?php endif; ?>

							<?php if ( $link_projet ) : ?>
								<a class="projets-thumb__cta"
								   href="<?php echo esc_url( $link_projet['url'] ); ?>"
								   target="<?php echo esc_attr( $link_projet['target'] ?: '_self' ); ?>"
								   rel="noopener">
									<span><?php echo esc_html( $link_projet['title'] ?: 'Voir le projet' ); ?></span>
									<?php echo $arrow_svg; // phpcs:ignore ?>
								</a>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $lenguages ) ) : ?>
							<ul class="projets-thumb__tags">
								<?php foreach ( $lenguages as $lang ) : ?>
									<li class="projets-thumb__tag"><?php echo esc_html( $lang['title_lenguages'] ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</article>
				<?php endwhile; ?>
			</div>
		</div>

	
	</div>
	<!-- Navegación -->
	<div class="projets-nav">
			<button class="projets-nav__btn projets-nav__btn--prev" type="button" aria-label="Projet précédent">
				<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<button class="projets-nav__btn projets-nav__btn--next" type="button" aria-label="Projet suivant">
				<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
</section>

<?php wp_reset_postdata(); ?>