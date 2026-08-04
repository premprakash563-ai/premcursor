(() => {
	const header = document.querySelector("[data-hf-header]");
	const toggle = document.querySelector("[data-hf-nav-toggle]");
	const nav = document.querySelector("[data-hf-nav]");
	const backTop = document.querySelector("[data-hf-back-top]");

	if (toggle && nav) {
		toggle.addEventListener("click", () => {
			const open = nav.classList.toggle("is-open");
			toggle.setAttribute("aria-expanded", open ? "true" : "false");
		});
	}

	let lastY = window.scrollY;
	let ticking = false;

	const onScroll = () => {
		const y = window.scrollY;
		if (header) {
			if (y > 140 && y > lastY) header.classList.add("is-hidden");
			else header.classList.remove("is-hidden");
		}
		if (backTop) backTop.hidden = y < 500;
		lastY = y;
		ticking = false;
	};

	window.addEventListener(
		"scroll",
		() => {
			if (!ticking) {
				window.requestAnimationFrame(onScroll);
				ticking = true;
			}
		},
		{ passive: true }
	);

	if (backTop) {
		backTop.addEventListener("click", () => {
			window.scrollTo({ top: 0, behavior: "smooth" });
		});
	}
})();
