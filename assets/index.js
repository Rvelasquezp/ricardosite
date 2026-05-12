// import Site styles
import "/assets/css/style.scss";

// import parts styles
import "/assets/css/parts/paragraph.scss";
import "/assets/css/parts/header.scss";
import "/assets/css/parts/social_icons.scss";

// import block styles
import "/assets/css/blocks/accordion.scss";
import "/assets/css/blocks/hamburguer.scss";
import "/assets/css/blocks/projets.scss";
import "/assets/css/blocks/language_switcher.scss";
import "/assets/css/blocks/pdf_buttons.scss";

// import parts styles (popup)
import "/assets/css/parts/popup.scss";

// import Swiper styles
import "swiper/css";

import Header from "/assets/js/header.js";

// import js libraries
import Swiper from "swiper";
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { ScrollSmoother } from "gsap/ScrollSmoother";
import { prepareAccordions } from "./js/prepareAccordions";
import  PrincipalBannerParallax from "./js/PrincipalBannerParallax";
import  HeaderShrink from "./js/HeaderShrink";
import projetsSlider from "./js/projetsSlider";
import initPopup from "./js/popup";



gsap.registerPlugin(ScrollTrigger, ScrollSmoother);

// create the scrollSmoother before your scrollTriggers
let scroller = ScrollSmoother.create({
	smooth: 1, // how long (in seconds) it takes to "catch up" to the native scroll position
	effects: true, // looks for data-speed and data-lag attributes on elements
	smoothTouch: 0, // much shorter smoothing time on touch devices (default is NO smoothing on touch devices)
	content: ".home_page",
});

// padding space header

// const headerMain = document.querySelector(".header-main");
// const wpAdminBar = document.getElementById("wpadminbar");
// const heroBanner = document.querySelector(".padding_space");

// if (heroBanner) {
// 	function setHeroPaddingTop() {
// 		let isMobile = window.innerWidth < 768; // puedes ajustar este valor
// 		let totalOffset = headerMain ? headerMain.clientHeight : 0;

// 		if (wpAdminBar) {
// 			if (isMobile) {
// 				heroBanner.style.paddingTop = `${totalOffset - 36}px `; // ejemplo: valor fijo para mobile
// 			} else {
// 				// Comportamiento en pantallas grandes
// 				heroBanner.style.paddingTop = `${totalOffset}px`;
// 			}
// 		} else {
// 			if (isMobile) {
// 				// Comportamiento en pantallas pequeñas
// 				heroBanner.style.paddingTop = `${totalOffset - 6}px`; // ejemplo: valor fijo para mobile
// 			} else {
// 				// Comportamiento en pantallas grandes
// 				heroBanner.style.paddingTop = `${totalOffset}px`;
// 			}
// 		}
// 	}

// 	setHeroPaddingTop();
// 	// window.addEventListener("resize", setHeroPaddingTop);
// 	ScrollTrigger.refresh();
// }

// padding space header

// contact form 7 stop submitting on select change
document.addEventListener("DOMContentLoaded", function () {
	const form = document.querySelector(".wpcf7 form");
	if (!form) return;

	const fields = form.querySelectorAll(
		'select, input[type="radio"], input[type="checkbox"], input[type="file"]'
	);

	fields.forEach((field) => {
		field.addEventListener("change", function (e) {
			// Prevenir validación automática si no se ha presionado el botón de enviar
			e.stopPropagation();
		});
	});
});

// contact form 7 stop submitting on select change

// Anchor Scroll
const anchorLinks = document.querySelectorAll("a[href^='/#'], a[href^='#']");
const header = document.querySelector("header");
const headerHeight = header ? header.clientHeight : 0;

anchorLinks.forEach((link) => {
	const hash = link.getAttribute("href").split("#")[1];
	if (!hash) return;

	const parentMenu = link.closest("ul.wp-block-navigation-submenu");
	const targetElement = document.querySelector("#" + hash);

	// Corrige href en submenús si es necesario
	if (parentMenu) {
		const parentLink = parentMenu.previousElementSibling;
		if (
			parentLink &&
			parentLink.href &&
			!link.href.includes(parentLink.href.split("#")[0])
		) {
			link.href = parentLink.href.split("#")[0] + "#" + hash;
		}
	}

	link.addEventListener("click", (e) => {
		if (!targetElement) {
			window.location.href = link.href;
			return;
		}

		e.preventDefault();

		// Cierra el menú mobile si está abierto
		const toggler = document.querySelector(".main-menu-toggler");
		const mobileNav = document.querySelector(".mobile-nav");

		if (toggler && mobileNav && toggler.classList.contains("menu-open")) {
			toggler.classList.remove("menu-open");
			mobileNav.classList.remove("menu-open");
			header.classList.remove("header-menu-open");

			// Animación opcional de cierre (descomenta si la quieres)
			gsap.to(mobileNav, {
				duration: 0.6,
				y: "-100%",
				opacity: 0,
				ease: "power2.inOut",
				onComplete: () => {
					mobileNav.classList.remove("menu-open");
					header.classList.remove("header-menu-open");
				},
			});
		}

		// Reactiva el scroll (si usas un scroller tipo Lenis o similar)
		if (typeof scroller !== "undefined") {
			scroller.paused(false);
		}

		const targetY =
			targetElement.getBoundingClientRect().top +
			window.pageYOffset -
			headerHeight;

		window.scrollTo({
			top: targetY,
			behavior: "smooth",
		});
	});
});

// Scroll automático si hay hash al cargar la página
window.addEventListener("load", () => {
	const anchor = window.location.hash;
	if (!anchor) return;

	const target = document.querySelector(anchor);
	if (target) {
		const targetY =
			target.getBoundingClientRect().top + window.pageYOffset - headerHeight;

		window.scrollTo({
			top: targetY,
			behavior: "smooth",
		});
	}
});

// Anchor Scroll

// Parallax animations optimizadas
const parallaxConfigs = [
	{ selector: ".image-fixed-animation", speed: "0.8" },
	{ selector: ".image-fixed-animation-more", speed: "1.1" },
	{ selector: ".image-fixed-animation-auto", speed: "auto" },
	{ selector: ".image-fixed-slow", speed: "0.5" },
];

parallaxConfigs.forEach(({ selector, speed }) => {
	const elements = document.querySelectorAll(selector);

	if (elements.length > 0) {
		elements.forEach((el) => {
			const img = el.querySelector("img");
			if (img) {
				scroller.effects(img, { speed });
			}
		});
	}
});

// Parallax animations optimizadas

// change social icons
const svgFacebook = document.querySelectorAll(".wp-social-link-facebook");

if (svgFacebook.length > 0) {
	for (let buttonf of svgFacebook) {
		buttonf.querySelector(".wp-block-social-link-anchor").innerHTML =
			'<svg id="Composant_239_2" data-name="Composant 239 – 2" xmlns="http://www.w3.org/2000/svg" width="14.33" height="27.44" viewBox="0 0 14.33 27.44"><path id="Tracé_38" data-name="Tracé 38" d="M89.3,27.441V14.94h4.269l.61-4.878H89.3V7.013c0-1.372.457-2.439,2.439-2.439H94.33V.152C93.72.152,92.2,0,90.519,0c-3.659,0-6.25,2.287-6.25,6.4v3.659H80V14.94h4.269v12.5Z" transform="translate(-80)" fill="#fff" fill-rule="evenodd"/></svg>';
	}
}

const svgInsta = document.querySelectorAll(".wp-social-link-instagram");

if (svgInsta.length > 0) {
	for (let buttong of svgInsta) {
		buttong.querySelector(".wp-block-social-link-anchor").innerHTML =
			'<svg id="Composant_238_2" data-name="Composant 238 – 2" xmlns="http://www.w3.org/2000/svg" width="27.441" height="27.44" viewBox="0 0 27.441 27.44"><path id="Tracé_40" data-name="Tracé 40" d="M13.72,2.439a42.069,42.069,0,0,1,5.488.152,7.071,7.071,0,0,1,2.592.457,5.354,5.354,0,0,1,2.592,2.592,7.071,7.071,0,0,1,.457,2.592c0,1.372.152,1.829.152,5.488a42.069,42.069,0,0,1-.152,5.488,7.071,7.071,0,0,1-.457,2.592A5.354,5.354,0,0,1,21.8,24.392a7.071,7.071,0,0,1-2.592.457c-1.372,0-1.829.152-5.488.152a42.068,42.068,0,0,1-5.488-.152,7.071,7.071,0,0,1-2.592-.457A5.354,5.354,0,0,1,3.049,21.8a7.071,7.071,0,0,1-.457-2.592c0-1.372-.152-1.829-.152-5.488a42.069,42.069,0,0,1,.152-5.488,7.071,7.071,0,0,1,.457-2.592A5.475,5.475,0,0,1,4.116,4.116,2.577,2.577,0,0,1,5.641,3.049a7.071,7.071,0,0,1,2.592-.457,42.069,42.069,0,0,1,5.488-.152M13.72,0A45.044,45.044,0,0,0,8.08.152a9.412,9.412,0,0,0-3.354.61A5.968,5.968,0,0,0,2.287,2.287,5.968,5.968,0,0,0,.762,4.726,6.946,6.946,0,0,0,.152,8.08,45.044,45.044,0,0,0,0,13.72a45.044,45.044,0,0,0,.152,5.641,9.412,9.412,0,0,0,.61,3.354,5.968,5.968,0,0,0,1.524,2.439,5.968,5.968,0,0,0,2.439,1.524,9.411,9.411,0,0,0,3.354.61,45.045,45.045,0,0,0,5.641.152,45.045,45.045,0,0,0,5.641-.152,9.412,9.412,0,0,0,3.354-.61,6.4,6.4,0,0,0,3.964-3.964,9.411,9.411,0,0,0,.61-3.354c0-1.524.152-1.982.152-5.641a45.045,45.045,0,0,0-.152-5.641,9.412,9.412,0,0,0-.61-3.354,5.968,5.968,0,0,0-1.524-2.439A5.968,5.968,0,0,0,22.715.762a9.412,9.412,0,0,0-3.354-.61A45.044,45.044,0,0,0,13.72,0m0,6.708A6.9,6.9,0,0,0,6.708,13.72,7.013,7.013,0,1,0,13.72,6.708m0,11.586A4.492,4.492,0,0,1,9.147,13.72,4.492,4.492,0,0,1,13.72,9.147a4.492,4.492,0,0,1,4.573,4.573,4.492,4.492,0,0,1-4.573,4.573M21.038,4.726A1.677,1.677,0,1,0,22.715,6.4a1.692,1.692,0,0,0-1.677-1.677" fill="#fff" fill-rule="evenodd"/></svg>';
	}
}

const svgTw = document.querySelectorAll(".wp-social-link-linkedin");

if (svgTw.length > 0) {
	for (let buttonh of svgTw) {
		buttonh.querySelector(".wp-block-social-link-anchor").innerHTML =
			'<svg id="Composant_240_2" data-name="Composant 240 – 2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="27.44" height="27.44" viewBox="0 0 27.44 27.44"><defs><clipPath id="clip-path"><rect id="Rectangle_1932" data-name="Rectangle 1932" width="27.44" height="27.44" fill="#fff"/></clipPath></defs><g id="Groupe_969" data-name="Groupe 969" clip-path="url(#clip-path)"><path id="Tracé_2590" data-name="Tracé 2590" d="M162.629,148.3v-10.05c0-4.939-1.063-8.712-6.826-8.712a5.956,5.956,0,0,0-5.385,2.95h-.069v-2.5H144.9V148.3h5.694v-9.09c0-2.4.446-4.7,3.4-4.7,2.916,0,2.95,2.71,2.95,4.836v8.918h5.694Z" transform="translate(-135.189 -120.858)" fill="#fff"/><rect id="Rectangle_1931" data-name="Rectangle 1931" width="5.694" height="18.316" transform="translate(0.446 9.124)" fill="#fff"/><path id="Tracé_2591" data-name="Tracé 2591" d="M3.293,0A3.31,3.31,0,1,0,6.586,3.293,3.294,3.294,0,0,0,3.293,0" fill="#fff"/></g></svg>';
	}
}
// change social icons

// Añadir animación a los títulos con clases específicas
// document.addEventListener("DOMContentLoaded", () => {
// 	const headings = document.querySelectorAll(
// 		".move_down, .move_up, .move_right, .move_left"
// 	);

// 	if (!headings.length) return; // Evita errores si no hay elementos

// 	const observer = new IntersectionObserver(
// 		(entries, observer) => {
// 			entries.forEach((entry) => {
// 				if (entry.isIntersecting) {
// 					entry.target.classList.add("animation");
// 					observer.unobserve(entry.target); // Deja de observar una vez animado
// 				}
// 			});
// 		},
// 		{
// 			threshold: 0.1, // Cuando el 10% del elemento es visible
// 		}
// 	);

// 	headings.forEach((heading) => observer.observe(heading));
// });

// con efecto cascada ((uno por uno ))
document.addEventListener("DOMContentLoaded", () => {
	const headings = document.querySelectorAll(
		".move_down, .move_up, .move_right, .move_left"
	);

	if (!headings.length) return;

	// Creamos un mapa para saber el orden DOM real
	const headingArray = [...headings];

	const observer = new IntersectionObserver(
		(entries, observer) => {
			// Filtrar solo los visibles
			const visibleEntries = entries.filter((entry) => entry.isIntersecting);

			// Ordenar según su posición en el DOM original
			visibleEntries.sort(
				(a, b) =>
					headingArray.indexOf(a.target) - headingArray.indexOf(b.target)
			);

			// Aplicar animación escalonada en orden DOM
			visibleEntries.forEach((entry, i) => {
				setTimeout(() => {
					entry.target.classList.add("animation");
					observer.unobserve(entry.target);
				}, i * 300);
			});
		},
		{ threshold: 0.1 }
	);

	headings.forEach((heading) => observer.observe(heading));
});

// Añadir animación a los títulos con clases específicas

// animation images
document.addEventListener("DOMContentLoaded", () => {
	const icons = document.querySelectorAll(
		".move_down img, .move_up img, .move_right img, .move_left img, .move_down svg, .move_up svg, .move_right svg, .move_left svg"
	);
	if (!icons.length) return;

	const iconArray = [...icons]; // Guardamos el orden DOM

	const iconObserver = new IntersectionObserver(
		(entries, observer) => {
			// Filtramos solo los que están visibles
			const visibleEntries = entries.filter((entry) => entry.isIntersecting);

			// Ordenamos por orden DOM original
			visibleEntries.sort(
				(a, b) => iconArray.indexOf(a.target) - iconArray.indexOf(b.target)
			);

			// Aplicamos delay escalonado
			visibleEntries.forEach((entry, i) => {
				setTimeout(() => {
					entry.target.classList.add("animation");
					observer.unobserve(entry.target);
				}, i * 400); // Delay entre íconos
			});
		},
		{ threshold: 0.1 }
	);

	icons.forEach((icon) => iconObserver.observe(icon));
});

// animation images

// Add animation cibizer

document.addEventListener("DOMContentLoaded", () => {
	// Seleccionamos la imagen que quieres animar
	const imageElement = document.querySelector(".animation_cibizer img");

	if(imageElement) {
	// Usamos GSAP para animar cuando el scroll llegue a la imagen
	gsap.fromTo(
		imageElement,
		{
			transform: "scale(0.2)", // Iniciamos con la imagen más pequeña
			opacity: 0, // Comenzamos con la opacidad a 0
		},
		{
			transform: "scale(1)", // Animamos hasta el tamaño original
			opacity: 1, // Finalizamos con opacidad completa
			duration: 1.2, // Duración de la animación
			ease: "cubic-bezier(0.5, -0.3, 0.3, 1.5)", // La curva de animación que proporcionas
			scrollTrigger: {
				trigger: imageElement, // El trigger es la imagen
				start: "top 80%", // El efecto empieza cuando la imagen está en el 80% del viewport
				end: "bottom top", // El efecto termina cuando la imagen sale del viewport
				toggleActions: "play none none none", // Define qué hacer al entrar/salir del viewport
				// markers: true, // Solo para depuración, muestra los puntos de inicio y fin de ScrollTrigger
			},
		}
	);
	}

});

// Add animation cibizer

// Add animation cibizer and rotate
// rotation: -15 → efecto suave.
// rotation: -90 → giro de cuarto de vuelta.
// rotation: -180 → media vuelta.
// rotation: -360 → una vuelta completa.

document.addEventListener("DOMContentLoaded", () => {
	// Seleccionamos la imagen que quieres animar
	const imageElement = document.querySelector(".animation_cibizer_rotate img");
	if(imageElement) {
		// Usamos GSAP para animar cuando el scroll llegue a la imagen
		gsap.fromTo(
			imageElement,
			{
				scale: 0.2, // Imagen más pequeña
				opacity: 0, // Invisible
				rotate: -360, // Rotación inicial
			},
			{
				scale: 1, // Tamaño original
				opacity: 1, // Opacidad completa
				rotate: 0, // Rotación a posición normal
				duration: 1.2,
				ease: "cubic-bezier(0.5, -0.3, 0.3, 1.5)",
				scrollTrigger: {
					trigger: imageElement,
					start: "top 80%",
					end: "bottom top",
					toggleActions: "play none none none",
					// markers: true,
				},
			}
		);
	}
});

// Add animation cibizer and rotate

Header(scroller);
prepareAccordions(gsap);
PrincipalBannerParallax();
HeaderShrink();
projetsSlider();
initPopup();

gsap.to("html", {
	autoAlpha: 1,
});
