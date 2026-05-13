import Swiper from "swiper";
import { Navigation, Thumbs } from "swiper/modules";

export default function projetsSlider() {
	const sections = document.querySelectorAll(".projets-block");
	if (sections.length > 0) {
		for (let section of sections) {
			const thumbsEl = section.querySelector(".projets-thumbs");
			const mainEl = section.querySelector(".projets-main");
			const prevEl = section.querySelector(".projets-nav__btn--prev");
			const nextEl = section.querySelector(".projets-nav__btn--next");

			if (!mainEl || !thumbsEl) continue;

			// Slider de thumbnails (cards pequeñas).
			// IMPORTANTE: el thumbs NO se loopea, Swiper lo sincroniza via realIndex.
			const thumbs = new Swiper(thumbsEl, {
				slidesPerView: 1.05,
				spaceBetween: 16,
				watchSlidesProgress: true,
				slideToClickedSlide: true,
				breakpoints: {
					640: { slidesPerView: 1.2, spaceBetween: 20 },
					1025: { slidesPerView: 1.6, spaceBetween: 24 },
					1520: { slidesPerView: 2.1, spaceBetween: 28 },
				},
			});

			// Slider principal (slide grande). Loop infinito.
			new Swiper(mainEl, {
				modules: [Navigation, Thumbs],
				slidesPerView: 1,
				spaceBetween: 0,
				loop: true,
				loopAdditionalSlides: 1,
				speed: 600,
				navigation: {
					nextEl: nextEl,
					prevEl: prevEl,
				},
				thumbs: {
					swiper: thumbs,
				},
				a11y: {
					prevSlideMessage: "Projet précédent",
					nextSlideMessage: "Projet suivant",
				},
				on: {
					slideChange: function () {
						thumbs.slideTo(this.realIndex);
					},
				},
			});
		}
	}
}