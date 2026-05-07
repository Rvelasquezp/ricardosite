import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export default function Header(scroller) {
	const toggler = document.querySelector(".main-menu-toggler");
	const mobileNav = document.querySelector("header.mobile-nav");
	const header = document.querySelector("header");

	toggler.addEventListener("click", () => {
		const menuOpen = mobileNav.classList.contains("menu-open");

		if (!menuOpen) {
			scroller.paused(true); // desactiva scroll si usas Locomotive o similar
			toggler.classList.add("menu-open");
			mobileNav.classList.add("menu-open");
			header.classList.add("header-menu-open");

			// Fade-in + movimiento suave desde arriba
			gsap.fromTo(
				mobileNav,
				{ y: "-100%", opacity: 0 },
				{
					duration: 0.6,
					y: "0%",
					opacity: 1,
					ease: "power2.out",
				}
			);
		} else {
			scroller.paused(false); // reactiva scroll
			toggler.classList.remove("menu-open");
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
	});
}
