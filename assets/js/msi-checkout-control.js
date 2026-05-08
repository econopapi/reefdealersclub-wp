/**
 * RDC MSI Control - Checkout Script
 *
 * @package RDC Custom Astra
 */
(function($) {
	'use strict';

	var config = window.RDCMSI || {};

	if (!config.enabled) {
		return;
	}

	var msiStatus = config.msiStatus;
	var allowedMonths = config.allowedMonths;
	var mixedCartMessage = config.mixedCartMessage;
	var disclaimerShown = false;
	var lastProcessedSelect = null;
	var lastProcessedOptionsCount = 0;

	function shouldRestrict() {
		return msiStatus === 'mixed' || msiStatus === 'none_msi';
	}

	function isAlreadyProcessed(selectEl) {
		if (!selectEl) {
			return false;
		}

		if (selectEl !== lastProcessedSelect) {
			return false;
		}

		var currentOptionsCount = selectEl.querySelectorAll('option[value]:not([value=""])').length;
		if (currentOptionsCount !== lastProcessedOptionsCount) {
			return false;
		}

		if (shouldRestrict()) {
			return selectEl.value === '1' && selectEl.disabled === true;
		}

		if (msiStatus === 'all_msi' && allowedMonths && allowedMonths.length > 0) {
			var options = selectEl.querySelectorAll('option');
			var hasDisallowed = false;
			options.forEach(function(opt) {
				var val = parseInt(opt.value, 10);
				if (opt.value === '' || opt.value === '1') {
					return;
				}

				if (!isNaN(val) && !allowedMonths.includes(val)) {
					hasDisallowed = true;
				}
			});
			return !hasDisallowed;
		}

		return true;
	}

	function lockInstallments(selectEl) {
		if (!selectEl) {
			return;
		}

		var optionOne = selectEl.querySelector('option[value="1"]');
		if (!optionOne) {
			return;
		}

		selectEl.value = '1';

		var options = selectEl.querySelectorAll('option');
		options.forEach(function(opt) {
			if (opt.value !== '1' && opt.value !== '') {
				opt.remove();
			}
		});

		selectEl.disabled = true;
		selectEl.style.opacity = '0.7';
		selectEl.style.cursor = 'not-allowed';

		var hiddenInstallments = document.getElementById('cardInstallments');
		if (hiddenInstallments) {
			hiddenInstallments.value = '1';
		}

		selectEl.dispatchEvent(new Event('change', { bubbles: true }));

		if (msiStatus === 'mixed' && !disclaimerShown) {
			showMixedCartDisclaimer(selectEl);
		}
	}

	function filterInstallmentOptions(selectEl) {
		if (!selectEl || !allowedMonths || allowedMonths.length === 0) {
			return;
		}

		var options = selectEl.querySelectorAll('option');
		options.forEach(function(opt) {
			var val = parseInt(opt.value, 10);
			if (opt.value === '' || opt.value === '1') {
				return;
			}

			if (!isNaN(val) && !allowedMonths.includes(val)) {
				opt.remove();
			}
		});
	}

	function showMixedCartDisclaimer(selectEl) {
		if (disclaimerShown) {
			return;
		}

		if (document.getElementById('rdc-msi-disclaimer')) {
			disclaimerShown = true;
			return;
		}

		var container = selectEl.closest('.mp-checkout-custom-installments-select-container') || selectEl.parentElement;
		if (!container) {
			return;
		}

		var disclaimer = document.createElement('div');
		disclaimer.id = 'rdc-msi-disclaimer';
		disclaimer.className = 'rdc-msi-disclaimer';
		disclaimer.innerHTML = '<span class="rdc-msi-disclaimer-icon">i</span>' +
			'<span class="rdc-msi-disclaimer-text"></span>';
		disclaimer.querySelector('.rdc-msi-disclaimer-text').textContent = mixedCartMessage;

		container.appendChild(disclaimer);
		disclaimerShown = true;
	}

	function processInstallmentsSelect() {
		var selectEl = document.getElementById('form-checkout__installments');
		if (!selectEl) {
			return;
		}

		var realOptions = selectEl.querySelectorAll('option[value]:not([value=""])');
		if (realOptions.length === 0) {
			return;
		}

		if (isAlreadyProcessed(selectEl)) {
			return;
		}

		if (shouldRestrict()) {
			lockInstallments(selectEl);
		} else if (msiStatus === 'all_msi' && allowedMonths && allowedMonths.length > 0) {
			filterInstallmentOptions(selectEl);
		}

		lastProcessedSelect = selectEl;
		lastProcessedOptionsCount = selectEl.querySelectorAll('option[value]:not([value=""])').length;
	}

	function resetState() {
		disclaimerShown = false;
		lastProcessedSelect = null;
		lastProcessedOptionsCount = 0;

		var existing = document.getElementById('rdc-msi-disclaimer');
		if (existing) {
			existing.remove();
		}
	}

	function init() {
		processInstallmentsSelect();

		setInterval(function() {
			processInstallmentsSelect();
		}, 2000);

		$(document.body).on('updated_checkout', function() {
			resetState();
			setTimeout(processInstallmentsSelect, 300);
			setTimeout(processInstallmentsSelect, 800);
			setTimeout(processInstallmentsSelect, 1500);
		});

		$(document.body).on('payment_method_selected', function() {
			resetState();
			setTimeout(processInstallmentsSelect, 300);
			setTimeout(processInstallmentsSelect, 800);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(jQuery);
