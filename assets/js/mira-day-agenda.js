document.addEventListener("DOMContentLoaded", function () {
	document.querySelectorAll(".more-details-link").forEach(function (link) {
		const modalId = link.getAttribute("data-modal");
		const modal = document.getElementById(modalId);
		const close = modal ? modal.querySelector(".close-modal") : null;
		const closeSpeaker = modal ? modal.querySelector(".close-speaker-modal") : null;

		if (modal && (close || closeSpeaker)) {
			link.addEventListener("click", function (e) {
				e.preventDefault();
				modal.style.display = "block";
			});

			if (close) {
				close.addEventListener("click", function (e) {
					e.preventDefault();
					modal.style.display = "none";
				});
			}

			if (closeSpeaker) {
				closeSpeaker.addEventListener("click", function (e) {
					e.preventDefault();
					// Find the closest parent .speaker-modal and hide it
					let speakerModal = closeSpeaker.closest('.speaker-modal');
					if (speakerModal) {
						speakerModal.style.display = "none";
					} else {
						// fallback to modal variable
						modal.style.display = "none";
					}
				});
			}

			window.addEventListener("click", function (event) {
				if (event.target === modal) {
					modal.style.display = "none";
				}
			});
		}
	});
});