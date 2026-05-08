(function($) {
	'use strict';

	function removeMsiProduct(button) {
		var $row = $(button).closest('tr');

		$row.fadeOut(200, function() {
			$row.remove();

			if ($('#rdc-msi-products-body tr[data-product-id]').length === 0) {
				$('#rdc-msi-products-body').html(
					'<tr class="rdc-msi-empty-state" id="rdc-msi-empty-row">' +
						'<td colspan="3">' +
							'<span class="dashicons dashicons-cart"></span>' +
							'<p>No hay productos configurados para MSI.<br>Usa el buscador de arriba para agregar productos.</p>' +
						'</td>' +
					'</tr>'
				);
				$('#rdc-msi-select-all-months').hide();
			}
		});
	}

	window.rdcRemoveMsiProduct = removeMsiProduct;

	$(function() {
		$('#rdc-msi-add-product').on('click', function() {
			var $select = $('#rdc-msi-product-search');
			var productId = $select.val();
			var productName = $select.find('option:selected').text();

			if (!productId) {
				window.alert('Por favor, selecciona un producto primero.');
				return;
			}

			if ($('#rdc-msi-products-body tr[data-product-id="' + productId + '"]').length) {
				window.alert('Este producto ya esta en la lista.');
				return;
			}

			$('#rdc-msi-empty-row').remove();
			$('#rdc-msi-select-all-months').show();

			var rowHtml = '<tr data-product-id="' + productId + '">' +
				'<td>' +
					'<div class="product-info">' +
						'<div>' +
							'<div class="product-name">' + $('<span>').text(productName).html() + '</div>' +
						'</div>' +
					'</div>' +
				'</td>' +
				'<td>' +
					'<div class="month-checks">';

			[3, 6, 9, 12].forEach(function(m) {
				rowHtml += '<label>' +
					'<input type="checkbox" ' +
						'name="rdc_msi_mp_products[' + productId + '][months][]" ' +
						'value="' + m + '" ' +
						'checked ' +
						'onchange="this.parentElement.classList.toggle(\\'checked\\', this.checked);" /> ' +
					m + 'm' +
				'</label>';
			});

			rowHtml += '</div></td>' +
				'<td>' +
					'<button type="button" class="remove-product" title="Quitar producto" onclick="rdcRemoveMsiProduct(this);">' +
						'<span class="dashicons dashicons-trash"></span>' +
					'</button>' +
				'</td></tr>';

			$('#rdc-msi-products-body').append(rowHtml);
			$('#rdc-msi-products-body tr[data-product-id="' + productId + '"] .month-checks label').addClass('checked');
			$select.val(null).trigger('change');
		});

		$('.bulk-month').on('change', function() {
			var month = $(this).val();
			var isChecked = $(this).is(':checked');

			$('#rdc-msi-products-body tr[data-product-id]').each(function() {
				var $checkbox = $(this).find('input[value="' + month + '"]');
				$checkbox.prop('checked', isChecked);
				$checkbox.parent().toggleClass('checked', isChecked);
			});
		});
	});
})(jQuery);
