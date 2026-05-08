import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

function PrincipalBannerParallax(selector, amounts) {
	const sections = document.querySelectorAll(selector);

	if (!sections.length) return;

	sections.forEach((section) => {
		const mm = gsap.matchMedia();

		const setup = (mediaQuery, xValue) => {
			mm.add(mediaQuery, () => {
				const tween = gsap.to(section, {
					backgroundPosition: `${xValue} center`,
					ease: "none",

					scrollTrigger: {
						trigger: section,
						start: "top bottom",
						end: "bottom top",
						scrub: true,
					},
				});

				return () => tween.kill();
			});
		};

		setup("(min-width: 1025px)", amounts.desktop);
		setup("(min-width: 768px) and (max-width: 1024px)", amounts.tablet);
		setup("(max-width: 767px)", amounts.mobile);
	});
}

export default function sectionParallax() {
	PrincipalBannerParallax(".principal_banner", {
		desktop: "100%",
		tablet: "60%",
		mobile: "30%",
	});

	PrincipalBannerParallax(".projets", {
		desktop: "80%",
		tablet: "50%",
		mobile: "30%",
	});

	PrincipalBannerParallax(".contact", {
		desktop: "70%",
		tablet: "40%",
		mobile: "20%",
	});
}