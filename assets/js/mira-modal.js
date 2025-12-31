jQuery(document).ready(function($) {
	
	// Handle modal triggers with data attributes
	$(document).on('click', '.mira-modal-trigger', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var modalId = $(this).data('modal-id');
		var modalContent = $(this).data('modal-content');
		
		if (modalId && modalContent) {
			miraOpenModal(modalId, modalContent);
		}
		
		return false;
	});
	
	// Function to open modal
	window.miraOpenModal = function(modalId, htmlContent) {
		var modal = $('#' + modalId);
		
		if (modal.length) {
			modal.find('.mira-modal-content').html(htmlContent);
			modal.css('display', 'flex');
			modal.addClass('active');
			$('body').css('overflow', 'hidden');
		}
	};
	
	// Function to close modal
	window.miraCloseModal = function(modalId) {
		var modal = $('#' + modalId);
		
		if (modal.length) {
			modal.css('display', 'none');
			modal.removeClass('active');
			$('body').css('overflow', '');
		}
	};
	
	// Close button
	$(document).on('click', '.mira-modal-close', function(e) {
		e.preventDefault();
		e.stopPropagation();
		var modal = $(this).closest('.mira-modal-overlay');
		var modalId = modal.attr('id');
		miraCloseModal(modalId);
	});
	
	// Click outside to close
	$(document).on('click', '.mira-modal-overlay', function(e) {
		if ($(e.target).hasClass('mira-modal-overlay')) {
			var modalId = $(this).attr('id');
			miraCloseModal(modalId);
		}
	});
	
	// ESC key to close
	$(document).on('keydown', function(e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			$('.mira-modal-overlay.active').each(function() {
				miraCloseModal($(this).attr('id'));
			});
		}
	});
});