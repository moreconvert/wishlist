jQuery(function ($) {

	/**
	 * Coupon actions
	 */
	var mct_coupon_actions = {

		init: function () {
			$('select#discount-type')
				.on('change', function () {
					// Get value
					var select_val = $(this).val();

					if ('percent' === select_val) {
						$('#coupon-amount').removeClass('wc_input_price').addClass('wc_input_decimal');
						$('#coupon-amount').siblings('.description').text(wlfmc_wishlist_admin.i18n_percent_description);
					} else {
						$('#coupon-amount').removeClass('wc_input_decimal').addClass('wc_input_price');
						$('#coupon-amount').siblings('.description').text(wlfmc_wishlist_admin.i18n_amount_description);
					}
				})
				.change();


		},


	};

	mct_coupon_actions.init();

	$(document.body)
		.on(
			'change',
			'.wc_input_price[type=text], .wc_input_decimal[type=text]',
			function () {
				var regex, decimalRegex,
					decimailPoint = wlfmc_wishlist_admin.decimal_point;

				if ($(this).is('.wc_input_price')) {
					decimailPoint = wlfmc_wishlist_admin.mon_decimal_point;
				}

				regex = new RegExp('[^\-0-9\%\\' + decimailPoint + ']+', 'gi');
				decimalRegex = new RegExp('\\' + decimailPoint + '+', 'gi');

				var value = $(this).val();
				var newvalue = value.replace(regex, '').replace(decimalRegex, decimailPoint);

				if (value !== newvalue) {
					$(this).val(newvalue);
				}
			}
		)

		.on(
			'keyup',
			// eslint-disable-next-line max-len
			'.wc_input_price[type=text], .wc_input_decimal[type=text]',
			function () {
				var regex, error, decimalRegex;
				var checkDecimalNumbers = false;

				if ($(this).is('.wc_input_price')) {
					checkDecimalNumbers = true;
					regex = new RegExp('[^\-0-9\%\\' + wlfmc_wishlist_admin.mon_decimal_point + ']+', 'gi');
					decimalRegex = new RegExp('[^\\' + wlfmc_wishlist_admin.mon_decimal_point + ']', 'gi');
					error = wlfmc_wishlist_admin.i18n_mon_decimal_error;
				} else {
					checkDecimalNumbers = true;
					regex = new RegExp('[^\-0-9\%\\' + wlfmc_wishlist_admin.decimal_point + ']+', 'gi');
					decimalRegex = new RegExp('[^\\' + wlfmc_wishlist_admin.decimal_point + ']', 'gi');
					error = wlfmc_wishlist_admin.i18n_decimal_error;
				}

				var value = $(this).val();
				var newvalue = value.replace(regex, '');

				// Check if newvalue have more than one decimal point.
				if (checkDecimalNumbers && 1 < newvalue.replace(decimalRegex, '').length) {
					newvalue = newvalue.replace(decimalRegex, '');
				}

				if (value !== newvalue) {
					showSnack(error);
				}

			}
		);

	$(document.body).on('click', '.wlfmc-built-wishlist-page:not(.disabled)', function (e) {
		e.preventDefault();
		var elem = $(this), oldtext = elem.text();

		elem.addClass('disabled');
		elem.text(wlfmc_wishlist_admin.i18n_making_page);

		$.ajax({
			url: wlfmc_wishlist_admin.ajax_url,
			data: {
				action: 'wlfmc_create_wishlist_page',
				key: wlfmc_wishlist_admin.ajax_nonce,
			},
			dataType: 'json',
			method: 'POST',
			success: function (data) {
				if (data && data.success) {
					location.reload(true);;
				}
			},
			error: function () {
				console.log(
					'We cant create page. Something wrong with AJAX response. Probably some PHP conflict.');
			},
			complete: function () {
				elem.removeClass('disabled');
				elem.text(oldtext);
			}
		});

		return false;
	});
	$(document.body).on('click', '.wlfmc-send-offer-email-test', function (e) {
		e.preventDefault();
		var elem = $(this), oldtext = elem.text();

		elem.addClass('disabled');
		elem.text(wlfmc_wishlist_admin.i18n_sending);

		$.ajax({
			url: wlfmc_wishlist_admin.ajax_url,
			data: {
				id :elem.data('id'),
				field:elem.data('field'),
				action: 'wlfmc_send_offer_email_test',
				key: wlfmc_wishlist_admin.ajax_nonce,
			},
			dataType: 'json',
			method: 'POST',
			success: function (data) {
				if(data && data.message)
					showSnack(data.message);
			},
			error: function () {
				console.log(
					'We cant Send email. Something wrong with AJAX response. Probably some PHP conflict.');
			},
			complete: function () {
				elem.removeClass('disabled');
				elem.text(oldtext);
			}
		});

		return false;
	})

	function showSnack(error) {
		var x = document.getElementById("snackbar");
		x.innerHTML = error;
		x.className = "show";
		setTimeout(function () {
			x.className = x.className.replace("show", "");
		}, 3000);
	}
});
