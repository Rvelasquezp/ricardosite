export function prepareAccordions(gsap) {
	const accordions = document.querySelectorAll(".accordion");

	if (!accordions.length) return;

	accordions.forEach((accordion) => {
		const content = accordion.querySelector(".accordionContent");
		const button = accordion.querySelector(".accordionToggle");

		if (!content || !button) return;

		const getHeight = () => content.scrollHeight;

		// estado inicial cerrado SIEMPRE
		gsap.set(content, {
			height: 0,
			overflow: "hidden",
		});

		const tl = gsap.timeline({
			paused: true,
			reversed: true,
			onReverseComplete: () => {
				accordion.classList.remove("open");
			},
			onStart: () => {
				accordion.classList.add("open");
			},
		});

		// animación normal
		tl.to(content, {
			height: () => getHeight(),
			duration: 0.65,
			ease: "expo.out",
		});

		// click
		button.addEventListener("click", () => {
			if (tl.reversed()) {
				tl.timeScale(0.6).play();   // abrir suave
			} else {
				tl.timeScale(1.2).reverse(); // cerrar más rápido
			}
		});

		// 🟢 FIX OPEN BY DEFAULT (SMOOTH)
		if (accordion.classList.contains("openByDefault")) {
			requestAnimationFrame(() => {
				tl.timeScale(0.7).play(); // 👈 suave desde inicio
			});
		}
	});
}