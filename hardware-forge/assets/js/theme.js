(() => {
	const header = document.querySelector("[data-hf-header]");
	const toggle = document.querySelector("[data-hf-nav-toggle]");
	const nav = document.querySelector("[data-hf-nav]");

	if (toggle && nav) {
		toggle.addEventListener("click", () => {
			const open = nav.classList.toggle("is-open");
			toggle.setAttribute("aria-expanded", open ? "true" : "false");
		});
	}

	if (!header) return;

	let lastY = window.scrollY;
	let ticking = false;

	const onScroll = () => {
		const y = window.scrollY;
		if (y > 120 && y > lastY) {
			header.classList.add("is-hidden");
		} else {
			header.classList.remove("is-hidden");
		}
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
})();
