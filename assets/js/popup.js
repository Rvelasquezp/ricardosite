import { gsap } from "gsap";

export default function initPopup() {
	const popup    = document.getElementById("popup");
	const closeBtn = document.querySelector(".popup-close");
	const pdfEmbed = document.querySelector(".popup-content");
	const openBtns = document.querySelectorAll(".pdf-btn--view");

	if (!popup || !closeBtn || !pdfEmbed) return;

	// Estado inicial oculto
	gsap.set(popup, { autoAlpha: 0, scale: 0.2 });
	popup.style.pointerEvents = "none";

	function openPopup(pdfUrl) {
		pdfEmbed.innerHTML = `
			<object data="${pdfUrl}" type="application/pdf" width="100%" height="100%">
				<p>Impossible d'afficher le PDF. <a href="${pdfUrl}" target="_blank">Télécharger</a>.</p>
			</object>`;
		document.body.style.overflow = "hidden";
		popup.style.pointerEvents = "auto";

		gsap.fromTo(popup,
			{ autoAlpha: 0, scale: 0.2 },
			{
				autoAlpha: 1,
				scale: 1,
				duration: 0.6,
				ease: "power2.inOut",
			}
		);
	}

	function closePopup() {
		gsap.to(popup, {
			autoAlpha: 0,
			scale: 0.2,
			duration: 0.4,
			ease: "power2.inOut",
			onComplete: () => {
				pdfEmbed.innerHTML = "";
				document.body.style.overflow = "";
				popup.style.pointerEvents = "none";
			},
		});
	}

	openBtns.forEach((btn) => {
		btn.addEventListener("click", () => {
			const url = btn.dataset.pdf;
			if (url) openPopup(url);
		});
	});

	closeBtn.addEventListener("click", closePopup);

	document.addEventListener("keydown", (e) => {
		if (e.key === "Escape" && popup.style.pointerEvents !== "none") closePopup();
	});

	popup.addEventListener("click", (e) => {
		if (e.target === popup) closePopup();
	});
}
