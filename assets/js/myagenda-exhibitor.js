/**
 * MyAgenda – Exhibitor List Integration
 *
 * Handles the "+ My Itinerary" buttons on the exhibitor list.
 * Stores exhibitor post IDs in the "ExhibitorItinerary" cookie.
 */
(function () {
	'use strict';

	var COOKIE_NAME = 'ExhibitorItinerary';
	var COOKIE_DAYS = 30;

	function getCookie(name) {
		var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
		return match ? decodeURIComponent(match[1]) : null;
	}

	function setCookie(name, value, days) {
		var expires = new Date(Date.now() + days * 864e5).toUTCString();
		document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/';
	}

	function getItinerary() {
		var raw = getCookie(COOKIE_NAME);
		if (!raw) return [];
		try { return JSON.parse(raw); } catch (e) { return []; }
	}

	function saveItinerary(ids) {
		setCookie(COOKIE_NAME, JSON.stringify(ids), COOKIE_DAYS);
	}

	function isInItinerary(id) {
		return getItinerary().indexOf(String(id)) !== -1;
	}

	function updateButtonState(btn, inList) {
		var labelAdd    = btn.getAttribute('data-label-add') || '+ My Itinerary';
		var labelRemove = btn.getAttribute('data-label-remove') || 'In Itinerary';
		if (inList) {
			btn.textContent = labelRemove;
			btn.classList.add('in-itinerary');
			btn.title = 'Click to remove from My Itinerary';
		} else {
			btn.textContent = labelAdd;
			btn.classList.remove('in-itinerary');
			btn.title = 'Click to add to My Itinerary';
		}
	}

	function initButtons() {
		var buttons = document.querySelectorAll('span.myagenda[data-exhibitor-id]');
		console.log('MyAgenda Exhibitor: found ' + buttons.length + ' itinerary buttons');
		buttons.forEach(function (btn) {
			var id = String(btn.getAttribute('data-exhibitor-id'));
			updateButtonState(btn, isInItinerary(id));

			btn.addEventListener('click', function (e) {
				e.preventDefault();
				var ids = getItinerary();
				var idx = ids.indexOf(id);
				if (idx === -1) {
					ids.push(id);
				} else {
					ids.splice(idx, 1);
				}
				saveItinerary(ids);
				updateButtonState(btn, idx === -1);
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initButtons);
	} else {
		initButtons();
	}

})();
