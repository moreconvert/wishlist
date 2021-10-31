/**
 * Main Smart WooCommerce Wishlist JS
 *
 * @author: MoreConvert
 * @package: Smart Wishlist For More Convert
 */
(function ($) {
	$(document).ready(function () {

		/* === MAIN INIT === */

		$(document).on('wlfmc_init', function () {
			var t = $(this),
				cart_redirect_after_add = (typeof (wc_add_to_cart_params) !== 'undefined' && wc_add_to_cart_params !== null) ? wc_add_to_cart_params.cart_redirect_after_add : '';

			t.on('click', '.add_to_wishlist', function (ev) {
				var t = $(this),
					product_id = t.attr('data-product-id'),
					el_wrap = $('.add-to-wishlist-' + product_id),
					filtered_data = null,
					data = {
						action: wlfmc_l10n.actions.add_to_wishlist_action,
						context: 'frontend',
						add_to_wishlist: product_id,
						product_type: t.data('product-type'),
						wishlist_id: t.data('wishlist-id'),
						fragments: retrieve_fragments(product_id)
					};

				// allow third party code to filter data
				if (filtered_data === $(document).triggerHandler('wlfmc_add_to_wishlist_data', [t, data])) {
					data = filtered_data;
				}

				ev.preventDefault();

				jQuery(document.body).trigger('adding_to_wishlist');


				if (!is_cookie_enabled()) {
					window.alert(wlfmc_l10n.labels.cookie_disabled);
					return;
				}

				$.ajax({
					type: 'POST',
					url: wlfmc_l10n.ajax_url,
					data: data,
					dataType: 'json',
					beforeSend: function () {
						block(t);
					},
					complete: function () {
						unblock(t);
					},
					success: function (response) {
						var response_result = response.result,
							response_message = response.message,
							show_toast = true,
							click_behavior = response.click_behavior;


						if (response_result === 'true' || response_result === 'exists') {
							if (typeof response.fragments !== 'undefined') {
								replace_fragments(response.fragments);
							}

							if (!wlfmc_l10n.multi_wishlist || wlfmc_l10n.hide_add_button) {
								el_wrap.find('.wlfmc-add-button').remove();
							}
							var elem = $('.add-to-wishlist-' + product_id),
								popup_id = elem.find('.wlfmc-popup').attr('id');

							if (popup_id) {
								show_toast = false;
								$('#' + popup_id).popup({
									closeelement: '#' + popup_id + '_close',
									absolute: false,
									color: '#fff',
									horizontal: elem.find('.wlfmc-popup').data('horizontal'),
									vertical: elem.find('.wlfmc-popup').data('vertical'),
									transition: 'all 0.3s',
								});
								$('#' + popup_id).popup('show');

							}


							el_wrap.addClass('exists');
						}
						if (show_toast)
							toastr.success(response_message);

						if (response_result === 'true' && click_behavior === 'add-redirect')
							window.location.href = response.wishlist_url;

						init_handling_after_ajax();

						$('body').trigger('added_to_wishlist', [t, el_wrap]);

					}

				});

				return false;
			});

			t.on('click', '.wlfmc_btn_login_need', function (ev) {
				ev.preventDefault();
				toastr.error(wlfmc_l10n.labels.login_need);
				return false;
			});

			t.on('click', '.wlfmc_wishlist_table .remove_from_wishlist', function (ev) {
				var t = $(this);

				ev.preventDefault();

				remove_item_from_wishlist(t);

				return false;
			});

			t.on('adding_to_cart', 'body', function (ev, button, data) {
				if (typeof button !== 'undefined' && typeof data !== 'undefined' && button.closest('.wlfmc_wishlist_table').length) {
					data.remove_from_wishlist_after_add_to_cart = button.closest('[data-row-id]').data('row-id');
					data.wishlist_id = button.closest('.wlfmc_wishlist_table').data('id');
					typeof wc_add_to_cart_params !== 'undefined' && (wc_add_to_cart_params.cart_redirect_after_add = wlfmc_l10n.redirect_to_cart);
				}
			});

			t.on('added_to_cart', 'body', function (ev, fragments, carthash, button) {
				if (typeof button !== 'undefined' && button.closest('.wlfmc_wishlist_table').length) {
					typeof wc_add_to_cart_params !== 'undefined' && (wc_add_to_cart_params.cart_redirect_after_add = cart_redirect_after_add);

					var tr = button.closest('[data-row-id]'),
						table = tr.closest('.wishlist-fragment'),
						options = table.data('fragment-options');

					button.removeClass('added');
					tr.find('.added_to_cart').remove();

					if (wlfmc_l10n.remove_from_wishlist_after_add_to_cart && options.is_user_owner) {
						tr.remove();
					}
				}
			});

			t.on('added_to_cart', 'body', function () {
				var messages = $('.woocommerce-message');

				if (messages.length === 0) {
					$('#wlfmc-wishlist-form').prepend(wlfmc_l10n.labels.added_to_cart_message);
				} else {
					messages.fadeOut(300, function () {
						$(this).replaceWith(wlfmc_l10n.labels.added_to_cart_message).fadeIn();
					});
				}
			});

			t.on('cart_page_refreshed', 'body', init_handling_after_ajax);

			t.on('click', '.delete_item', function () {
				var t = $(this),
					product_id = t.attr('data-product-id'),
					item_id = t.data('item-id'),
					el_wrap = $('.add-to-wishlist-' + product_id),
					data = {
						action: wlfmc_l10n.actions.delete_item_action,
						context: 'frontend',
						item_id: item_id,
						fragments: retrieve_fragments(product_id)
					};

				$.ajax({
					url: wlfmc_l10n.ajax_url,
					data: data,
					dataType: 'json',
					beforeSend: function () {
						block(t);
					},
					complete: function () {
						unblock(t);
					},
					method: 'post',
					success: function (response) {
						var fragments = response.fragments,
							response_message = response.message;


						if (!t.closest('.wlfmc-remove-button').length) {
							toastr.error(response_message);
						}

						if (typeof fragments !== 'undefined') {
							replace_fragments(fragments);
						}

						init_handling_after_ajax();

						$('body').trigger('removed_from_wishlist', [t, el_wrap]);
					}
				});

				return false;
			});

			t.on('change', '#bulk_add_to_cart', function () {
				var t = $(this),
					checkboxes = t.closest('.wlfmc_wishlist_table').find('[data-row-id]').find('input[type="checkbox"]:not(:disabled)');

				if (t.is(':checked')) {
					checkboxes.prop('checked', 'checked').change();
				} else {

					checkboxes.prop('checked', false).change();
				}
			});

			t.on('submit', '.wlfmc-popup-form', function () {
				return false;
			});


			t.on('found_variation', function (ev, variation) {
				var t = $(ev.target),
					product_id = t.data('product_id'),
					variation_id = variation.variation_id,
					//targets = $('[data-product-id="' + product_id + '"]').add('[data-original-product-id="' + product_id + '"]'),
					target1 = $('.wlfmc-add-to-wishlist')
						.find('[data-product-id="' + product_id + '"]'),
					target2 = $('.wlfmc-add-to-wishlist')
						.find('[data-original-product-id="' + product_id + '"]'),
					targets = target1.add(target2),
					fragments = targets.closest('.wishlist-fragment').filter(':visible');

				if (!product_id || !variation_id || !targets.length) {
					return;
				}

				targets.each(function () {
					var t = $(this),
						container = t.closest('.wlfmc-add-to-wishlist'),
						options;

					t.attr('data-original-product-id', product_id);
					t.attr('data-product-id', variation_id);

					if (container.length) {
						options = container.data('fragment-options');

						if (typeof options !== 'undefined') {
							options.product_id = variation_id;
							container.data('fragment-options', options);
						}

						container
							.removeClass(function (i, classes) {
								return classes.match(/add-to-wishlist-\S+/g).join(' ');
							})
							.addClass('add-to-wishlist-' + variation_id)
							.attr('data-fragment-ref', variation_id);
					}
				});

				if (!wlfmc_l10n.reload_on_found_variation) {
					return;
				}

				block(fragments);

				load_fragments({
					fragments: fragments,
					firstLoad: false
				});
			});

			t.on('reset_data', function (ev) {
				var t = $(ev.target),
					product_id = t.data('product_id'),
					targets = $('[data-original-product-id="' + product_id + '"]'),
					fragments = targets.closest('.wishlist-fragment').filter(':visible');

				if (!product_id || !targets.length) {
					return;
				}

				targets.each(function () {
					var t = $(this),
						container = t.closest('.wlfmc-add-to-wishlist'),
						variation_id = t.attr('data-product-id'),
						options;

					t.attr('data-product-id', product_id);
					t.attr('data-original-product-id', '');

					if (container.length) {
						options = container.data('fragment-options');

						if (typeof options !== 'undefined') {
							options.product_id = product_id;
							container.data('fragment-options', options);
						}

						container
							.removeClass('add-to-wishlist-' + variation_id)
							.addClass('add-to-wishlist-' + product_id)
							.attr('data-fragment-ref', product_id);
					}
				});

				if (!wlfmc_l10n.reload_on_found_variation) {
					return;
				}

				block(fragments);

				load_fragments({
					fragments: fragments,
					firstLoad: false
				});

			});

			t.on('wlfmc_reload_fragments', load_fragments);

			t.on('wlfmc_fragments_loaded', function (ev, original, update, firstLoad) {
				if (!firstLoad) {
					return;
				}

				$('.variations_form').find('.variations select').last().change();
			});

			t.on('click', '.wlfmc-popup-feedback .close-popup', function (ev) {
				ev.preventDefault();

				close_popup();
			});

			init_wishlist_popup();

			init_quantity();

			init_checkbox_handling();

			init_copy_wishlist_link();


		}).trigger('wlfmc_init');


		/* === INIT FUNCTIONS === */

		/**
		 * Init popup for all links with the plugin that open a popup
		 *
		 * @return void
		 */
		function init_wishlist_popup() {

			// add & remove class to body when popup is opened
			var callback = function (node, op) {
					if (typeof node.classList !== 'undefined' && node.classList.contains('wlfmc-overlay')) {
						var method = 'remove' === op ? 'removeClass' : 'addClass';

						$('body')[method]('wlfmc-with-popup');
					}
				},
				callbackAdd = function (node) {
					callback(node, 'add');
				},
				callbackRemove = function (node) {
					callback(node, 'remove');
				},
				observer = new MutationObserver(function (mutationsList) {
					for (var i in mutationsList) {
						var mutation = mutationsList[i];
						if (mutation.type === 'childList') {
							typeof mutation.addedNodes !== 'undefined' && mutation.addedNodes.forEach(callbackAdd);

							typeof mutation.removedNodes !== 'undefined' && mutation.removedNodes.forEach(callbackRemove);
						}
					}
				});

			observer.observe(document.body, {
				childList: true
			});
		}


		/**
		 * Init checkbox handling
		 *
		 * @return void
		 */
		function init_checkbox_handling() {
			var checkboxes = $('.wlfmc_wishlist_table').find('.product-checkbox input[type="checkbox"]');

			checkboxes.off('change').on('change', function () {
				var t = $(this),
					p = t.parent();

				p.removeClass('checked')
					.removeClass('unchecked')
					.addClass(t.is(':checked') ? 'checked' : 'unchecked');
			}).trigger('change');
		}


		/**
		 * Init js handling on wishlist table items after ajax update
		 *
		 * @return void
		 */
		function init_handling_after_ajax() {

			init_checkbox_handling();
			init_quantity();
			init_copy_wishlist_link();

			$(document).trigger('wlfmc_init_after_ajax');
		}


		/**
		 * Handle quantity input change for each wishlist item
		 *
		 * @return void
		 */
		function init_quantity() {
			var jqxhr,
				timeout;

			$('.wlfmc_wishlist_table').on('change', '.product-quantity :input', function () {
				var t = $(this),
					row = t.closest('[data-row-id]'),
					product_id = row.data('row-id'),
					table = t.closest('.wlfmc_wishlist_table'),
					token = table.data('token');

				clearTimeout(timeout);

				// set add to cart link to add specific qty to cart
				row.find('.add_to_cart').attr('data-quantity', t.val());

				timeout = setTimeout(function () {
					if (jqxhr) {
						jqxhr.abort();
					}

					jqxhr = $.ajax({
						beforeSend: function () {
							block(table);
						},
						complete: function () {
							unblock(table);
						},
						data: {
							action: wlfmc_l10n.actions.update_item_quantity,
							context: 'frontend',
							product_id: product_id,
							wishlist_token: token,
							quantity: t.val()
						},
						method: 'POST',
						url: wlfmc_l10n.ajax_url
					});
				}, 1000);
			});
		}

		/**
		 * Init handling for copy button
		 *
		 * @return void
		 */
		function init_copy_wishlist_link() {
			$('.copy-trigger').on('click', function () {

				var obj_to_copy = $('.copy-target');

				if (obj_to_copy.length > 0) {
					if (obj_to_copy.is('input')) {

						if (isOS()) {

							obj_to_copy[0].setSelectionRange(0, 9999);
						} else {
							obj_to_copy.select();
						}
						document.execCommand('copy');
					} else {

						var hidden = $('<input/>', {
							val: obj_to_copy.text(),
							type: 'text'
						});

						$('body').append(hidden);

						if (isOS()) {
							hidden[0].setSelectionRange(0, 9999);
						} else {
							hidden.select();
						}
						document.execCommand('copy');

						hidden.remove();
					}
				}
			});
		}


		/* === EVENT HANDLING === */


		/**
		 * Remove a product from the wishlist.
		 *
		 * @param el
		 * @return void
		 */
		function remove_item_from_wishlist(el) {
			var table = el.parents('.wlfmc_wishlist_table'),
				row = el.parents('[data-row-id]'),
				data_row_id = row.data('row-id'),
				wishlist_id = table.data('id'),
				wishlist_token = table.data('token'),
				data = {
					action: wlfmc_l10n.actions.remove_from_wishlist_action,
					context: 'frontend',
					remove_from_wishlist: data_row_id,
					wishlist_id: wishlist_id,
					wishlist_token: wishlist_token,
					fragments: retrieve_fragments(data_row_id)
				};

			$.ajax({
				beforeSend: function () {
					block(table);
				},
				complete: function () {
					unblock(table);
				},
				data: data,
				method: 'post',
				success: function (data) {
					if (typeof data.fragments !== 'undefined') {
						replace_fragments(data.fragments);
					}

					init_handling_after_ajax();

					$('body').trigger('removed_from_wishlist', [el, row]);
				},
				url: wlfmc_l10n.ajax_url
			});
		}


		/* === UTILS === */


		/**
		 * Block item if possible
		 *
		 * @param item jQuery object
		 * @return void
		 */
		function block(item) {
			if (typeof $.fn.block !== 'undefined') {
				item.fadeTo('400', '0.6').block({
					message: null,
					overlayCSS: {
						background: 'transparent url(' + wlfmc_l10n.ajax_loader_url + ') no-repeat center',
						backgroundSize: '40px 40px',
						opacity: 1
					}
				});
			}
		}

		/**
		 * Unblock item if possible
		 *
		 * @param item jQuery object
		 * @return void
		 */
		function unblock(item) {
			if (typeof $.fn.unblock !== 'undefined') {
				item.stop(true).css('opacity', '1').unblock();
			}
		}

		/**
		 * Check if cookies are enabled
		 *
		 * @return bool
		 */
		function is_cookie_enabled() {
			if (navigator.cookieEnabled) {
				return true;
			}

			// set and read cookie
			document.cookie = 'cookietest=1';
			var ret = document.cookie.indexOf('cookietest=') !== -1;

			// delete cookie
			document.cookie = 'cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT';

			return ret;
		}

		/**
		 * Retrieve fragments that need to be refreshed in the page
		 *
		 * @param search string Ref to search among all fragments in the page
		 * @return object Object containing a property for each fragments that matches search
		 */
		function retrieve_fragments(search) {
			var options = {},
				fragments = null;

			if (search) {
				if (typeof search === 'object') {
					search = $.extend({
						fragments: null,
						s: '',
						container: $(document),
						firstLoad: false
					}, search);

					if (!search.fragments) {
						fragments = search.container.find('.wishlist-fragment');
					} else {
						fragments = search.fragments;
					}

					if (search.s) {
						fragments = fragments.not('[data-fragment-ref]').add(fragments.filter('[data-fragment-ref="' + search.s + '"]'));
					}

					if (search.firstLoad) {
						fragments = fragments.filter('.on-first-load');
					}
				} else {
					fragments = $('.wishlist-fragment');

					if (typeof search === 'string' || typeof search === 'number') {
						fragments = fragments.not('[data-fragment-ref]').add(fragments.filter('[data-fragment-ref="' + search + '"]'));
					}
				}
			} else {
				fragments = $('.wishlist-fragment');
			}

			if (fragments.length) {
				fragments.each(function () {
					var t = $(this),
						id = t.attr('class').split(' ').filter((val) => {
							return val.length && val !== 'exists';
						}).join(wlfmc_l10n.fragments_index_glue);

					options[id] = t.data('fragment-options');
				});
			} else {
				return null;
			}

			return options;
		}

		/**
		 * Load fragments on page loading
		 *
		 * @param search string Ref to search among all fragments in the page
		 */
		function load_fragments(search) {
			search = $.extend({
				firstLoad: true
			}, search);

			var fragments = retrieve_fragments(search);

			if (!fragments) {
				return;
			}

			$.ajax({
				data: {
					action: wlfmc_l10n.actions.load_fragments,
					context: 'frontend',
					fragments: fragments
				},
				method: 'post',
				success: function (data) {
					if (typeof data.fragments !== 'undefined') {
						replace_fragments(data.fragments);

						init_handling_after_ajax();

						$(document).trigger('wlfmc_fragments_loaded', [fragments, data.fragments, search.firstLoad]);

					}
				},
				url: wlfmc_l10n.ajax_url
			});
		}

		/**
		 * Replace fragments with template received
		 *
		 * @param fragments array Array of fragments to replace
		 */
		function replace_fragments(fragments) {
			$.each(fragments, function (i, v) {
				var itemSelector = '.' + i.split(wlfmc_l10n.fragments_index_glue).filter((val) => {
						return val.length && val !== 'exists' && val !== 'with-count';
					}).join('.'),
					toReplace = $(itemSelector);

				// find replace tempalte
				var replaceWith = $(v).filter(itemSelector);

				if (!replaceWith.length) {
					replaceWith = $(v).find(itemSelector);
				}

				if (toReplace.length && replaceWith.length) {
					toReplace.replaceWith(replaceWith);
				}
			});
		}

		/**
		 * Check if device is an IOS device
		 */
		function isOS() {
			return navigator.userAgent.match(/ipad|iphone/i);
		}

		/**
		 * Check if passed value could be considered true
		 */
		function isTrue(value) {
			return true === value || 'yes' === value || '1' === value || 1 === value;
		}
	});
})(jQuery);
