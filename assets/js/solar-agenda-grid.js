document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".more-details-link").forEach(function (link) {
	const modalId = link.getAttribute("data-modal");
	const modal = document.getElementById(modalId);
	const close = modal ? modal.querySelector(".close-modal") : null;

	if (modal && close) {
	  link.addEventListener("click", function (e) {
		e.preventDefault();
		modal.style.display = "block";
	  });

	  close.addEventListener("click", function (e) {
		e.preventDefault();
		modal.style.display = "none";
	  });

	  window.addEventListener("click", function (event) {
		if (event.target === modal) {
		  modal.style.display = "none";
		}
	  });
	}
  });
});