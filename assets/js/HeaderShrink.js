import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export default function HeaderShrink() {
	const header = document.querySelector("header.header-main");
	if (!header) return;

	const headerColumns = header.querySelector(".header-main-columns");
	const logoImg = header.querySelector(".wp-block-site-logo img");
	const mobileNav = document.querySelector("header.mobile-nav");
	const heroBanner = document.querySelector(".padding_space");

	if (!headerColumns || !logoImg) return;

	let scrollTrigger = null;
	let naturalHeaderHeight = 0;

	// ─── Mide la altura natural (sin shrink aplicado) ──────────
	const measureNaturalHeight = () => {
		const wasShrunk = header.classList.contains("is-shrunk");
		header.classList.remove("is-shrunk");
		gsap.set([headerColumns, logoImg, header], { clearProps: "all" });

		void header.offsetHeight; // force reflow

		const h = header.getBoundingClientRect().height;
		if (wasShrunk) header.classList.add("is-shrunk");
		return h;
	};

	// ─── Aplica padding-top al menú mobile y (si <1200px) al hero ──
	const applyPaddings = () => {
		// Mobile nav siempre recibe el padding
		if (mobileNav) {
			mobileNav.style.paddingTop = `${naturalHeaderHeight}px`;
		}

		// Hero solo si la pantalla es menor a 1200px
		if (heroBanner) {
			if (window.innerWidth < 1200) {
				heroBanner.style.paddingTop = `${naturalHeaderHeight}px`;
			} else {
				heroBanner.style.paddingTop = ""; // limpia el inline en desktop
			}
		}
	};

	// ─── Construye el timeline ─────────────────────────────────
	const buildTimeline = () => {
		if (scrollTrigger) {
			scrollTrigger.kill();
			scrollTrigger = null;
		}

		naturalHeaderHeight = measureNaturalHeight();
		applyPaddings();

		gsap.set([header, headerColumns, logoImg], { transition: "none" });

		const w = window.innerWidth;
		const config =
			w <= 768
				? { logoMax: "32vw", padding: "1.5vh" }
				: w <= 1024
				? { logoMax: "14vw", padding: "1.5vh" }
				: { logoMax: "5.5vw", padding: "1.2vh" };

		const tl = gsap.timeline({
			scrollTrigger: {
				trigger: document.body,
				start: "top top",
				end: "+=150",
				scrub: 0.6,
				invalidateOnRefresh: true,
				onRefresh: () => {
					if (!header.classList.contains("is-shrunk")) {
						naturalHeaderHeight = measureNaturalHeight();
						applyPaddings();
					}
				},
				onUpdate: (self) => {
					if (self.progress > 0.05) header.classList.add("is-shrunk");
					else header.classList.remove("is-shrunk");
				},
			},
		});

		tl.to(
			headerColumns,
			{
				paddingTop: config.padding,
				paddingBottom: config.padding,
				backgroundColor: "rgba(26, 28, 33, 0.85)",
				ease: "none",
			},
			0
		)
			.to(
				logoImg,
				{
					maxWidth: config.logoMax,
					scale: 0.95,
					transformOrigin: "left center",
					ease: "none",
				},
				0
			)
			.to(
				header,
				{
					boxShadow: "0 6px 24px rgba(0, 0, 0, 0.25)",
					backdropFilter: "blur(8px)",
					webkitBackdropFilter: "blur(8px)",
					ease: "none",
				},
				0
			);

		scrollTrigger = tl.scrollTrigger;
		ScrollTrigger.refresh();
	};

	// ─── Inicialización ────────────────────────────────────────
	if (logoImg.complete && logoImg.naturalHeight !== 0) {
		buildTimeline();
	} else {
		logoImg.addEventListener("load", buildTimeline, { once: true });
		setTimeout(() => {
			if (!scrollTrigger) buildTimeline();
		}, 500);
	}

	// ─── Resize / orientation ──────────────────────────────────
	let resizeTimer;
	const onResize = () => {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(buildTimeline, 200);
	};
	window.addEventListener("resize", onResize);
	window.addEventListener("orientationchange", onResize);

	// ─── Carga de fuentes ──────────────────────────────────────
	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(() => {
			if (!header.classList.contains("is-shrunk")) {
				naturalHeaderHeight = measureNaturalHeight();
				applyPaddings();
				ScrollTrigger.refresh();
			}
		});
	}

	// ─── ResizeObserver del header ─────────────────────────────
	if (typeof ResizeObserver !== "undefined") {
		let lastH = 0;
		const ro = new ResizeObserver(() => {
			if (header.classList.contains("is-shrunk")) return;

			const h = header.getBoundingClientRect().height;
			if (Math.abs(h - lastH) > 1 && Math.abs(h - naturalHeaderHeight) > 1) {
				lastH = h;
				naturalHeaderHeight = h;
				applyPaddings();
				ScrollTrigger.refresh();
			}
		});
		ro.observe(header);
	}

	// ─── Refresh final en window load ──────────────────────────
	window.addEventListener("load", () => {
		if (!header.classList.contains("is-shrunk")) {
			naturalHeaderHeight = measureNaturalHeight();
			applyPaddings();
			ScrollTrigger.refresh();
		}
	});
}