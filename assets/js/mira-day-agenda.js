document.addEventListener("DOMContentLoaded", function () {
  // Attach modal open/close for both .more-details-link and .speaker-img-clickable

  function anyModalOpen() {
    return Array.from(document.querySelectorAll('.modal, .speaker-modal')).some(function(modal) {
      return modal.style.display === 'block';
    });
  }

  function attachModalHandlers(selector, modalClass) {
    document.querySelectorAll(selector).forEach(function (el) {
      const modalId = el.getAttribute("data-modal");
      const modal = document.getElementById(modalId);
      const close = modal ? modal.querySelector(".close-modal") : null;
      let closeSpeaker = null;
      if (modalClass === "speaker-modal" && modal) {
        closeSpeaker = modal.querySelector(".close-speaker-modal");
      }
      if (modal) {
        el.addEventListener("click", function (e) {
          e.preventDefault();
          modal.style.display = "block";
          document.body.style.overflow = "hidden";
          console.log('Modal opened:', modalId, 'Type:', modalClass);
          // Ensure speaker-modal overlay dims the background and covers viewport
          if (modalClass === "speaker-modal") {
            modal.style.position = "fixed";
            modal.style.zIndex = "9999";
            modal.style.left = "0px";
            modal.style.top = "0px";
            modal.style.width = "100vw";
            modal.style.height = "100vh";
            modal.style.background = "rgba(0, 0, 0, 0.5)";
            modal.style.display = "block";
          }
        });
        function closeModal() {
          modal.style.display = "none";
          console.log('Modal closed:', modal.id);
          // Only unlock scroll if no modals are open
          if (!anyModalOpen()) {
            document.body.style.overflow = "";
            console.log('Scroll unlocked');
          }
        }
        if (close) {
          close.addEventListener("click", function (e) {
            e.preventDefault();
            closeModal();
          });
        }
        if (closeSpeaker) {
          closeSpeaker.addEventListener("click", function (e) {
            e.preventDefault();
            closeModal();
          });
        }
        modal.addEventListener("click", function (event) {
          if (event.target === modal) {
            closeModal();
          }
        });
      }
    });
  }

  attachModalHandlers(".more-details-link", "modal");
  attachModalHandlers(".speaker-img-clickable", "speaker-modal");
  attachModalHandlers(".speaker-name-clickable", "speaker-modal");

  // Attach keydown event ONCE for all modals
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      document.querySelectorAll('.modal, .speaker-modal').forEach(function(modal) {
        if (modal.style.display === 'block') {
          modal.style.display = 'none';
        }
      });
      // Only unlock scroll if no modals are open
      if (!anyModalOpen()) {
        document.body.style.overflow = "";
      }
    }
  });
});