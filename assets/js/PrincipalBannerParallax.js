import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export default function PrincipalBannerParallax() {
	const banner = document.querySelector(".principal_banner");
	if (!banner) return;

	const mm = gsap.matchMedia();

	// 🖥️ Desktop: parallax fuerte
	mm.add("(min-width: 1025px)", () => {
		const tween = gsap.to(banner, {
			backgroundPositionX: "100%",
			ease: "none",
			scrollTrigger: {
				trigger: banner,
				start: "top bottom",
				end: "bottom top",
				scrub: true,
			},
		});
		return () => tween.kill(); // limpieza al cambiar de breakpoint
	});

	// 💻 Tablet: parallax suave
	mm.add("(min-width: 768px) and (max-width: 1024px)", () => {
		const tween = gsap.to(banner, {
			backgroundPositionX: "60%",
			ease: "none",
			scrollTrigger: {
				trigger: banner,
				start: "top bottom",
				end: "bottom top",
				scrub: true,
			},
		});
		return () => tween.kill();
	});

	// 📱 Mobile: parallax mínimo (o lo desactivas si prefieres)
	mm.add("(max-width: 767px)", () => {
		const tween = gsap.to(banner, {
			backgroundPositionX: "30%",
			ease: "none",
			scrollTrigger: {
				trigger: banner,
				start: "top bottom",
				end: "bottom top",
				scrub: true,
			},
		});
		return () => tween.kill();
	});

	// 🤚 Si quieres desactivar en móvil completamente, reemplaza el bloque mobile por:
	// mm.add("(max-width: 767px)", () => {
	//     gsap.set(banner, { backgroundPositionX: "0%" });
	// });
}