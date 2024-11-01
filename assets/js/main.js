Frames.addEventHandler(
	Frames.Events.FRAME_BLUR,
	function (event) {
		if (Frames.isCardValid() && jQuery('.sp_cko_token').val() == '') {
			jQuery(".simpaisa_cko_loader").show();
			jQuery("#place_order").prop('disabled', true);
			Frames.submitCard().then((e) => {
				var CKObin = e.bin
				var CKOtoken = e.token
				if(CKOtoken){
					jQuery('.sp_cko_token').val(CKOtoken);
				}

				var simpaisa_bin_ajax_url = (typeof simpaisa_bin_ajax !== 'undefined') ? simpaisa_bin_ajax : "";
				if (e.bin && simpaisa_bin_ajax_url != "") {
					jQuery('.sp_cko_bin').val(CKObin);
					jQuery.ajax({
						data: {
							action: "simpaisa_card_bin_discount",
							bin_number: CKObin
						},
						type: 'POST',
						url: simpaisa_bin_ajax,
						dataType: "json",
						beforeSend: function () {
							jQuery("#loader").show();
						},
						complete: function () {
							jQuery("#loader").hide();
						},
						success: function (response) {
							jQuery(".simpaisa_cko_loader").hide();
							jQuery("#place_order").prop('disabled', false);
							if (response.status || response.status == 'true') {
								jQuery("[name='simpaisa_card_discount']").remove();
								if (!jQuery('[name="simpaisa_card_discount"]').length) {
									jQuery(".woocommerce-checkout").append('<input type="hidden" name="simpaisa_card_discount" value="' + response.discount + '">');
									jQuery('body').trigger('update_checkout');
									jQuery('body').on('updated_checkout', function () {
										jQuery(".card-number").show();
										jQuery(".card-number").html('************' + e.last4);
										jQuery(".card-frame").hide();
										jQuery(".sp_cko_checkbox").hide();
										jQuery('.sp_cko_bin').val(CKObin);
										jQuery('.sp_cko_token').val(CKOtoken);
									});
								}
							} else {
								jQuery("[name='simpaisa_card_discount']").remove();
								if (!jQuery('[name="simpaisa_card_discount"]').length) {
									jQuery(".woocommerce-checkout").append('<input type="hidden" name="simpaisa_card_discount" value="0">');
									jQuery('body').trigger('update_checkout');
									jQuery('body').on('updated_checkout', function () {
										jQuery(".card-number").show();
										jQuery(".card-number").html('************' + e.last4);
										jQuery(".card-frame").hide();
										jQuery(".sp_cko_checkbox").hide();
										jQuery('.sp_cko_bin').val(CKObin);
										jQuery('.sp_cko_token').val(CKOtoken);
									});
								}
							}
						},
						error: function (jqXHR, textStatus, errorThrown) {
							jQuery(".simpaisa_cko_loader").hide();
							jQuery("#place_order").prop('disabled', false);
						}

					})

				} else {
					jQuery(".simpaisa_cko_loader").hide();
					jQuery("#place_order").prop('disabled', false);
				}
			}, (reason) => {
				jQuery("#place_order").prop('disabled', false);
				console.error(reason); // Error!
			})

		} else if (!Frames.isCardValid() && jQuery('.sp_cko_token').val() != '') {
			jQuery('.sp_cko_token').val('');
			Frames.enableSubmitForm();
		}
	}


)

jQuery(function () {

	var checkout_form = jQuery('form.woocommerce-checkout');
	checkout_form.on('checkout_place_order');

	jQuery(document.body).on('change', 'input[name="payment_method"]', function () {
		if (jQuery(this).val() != "simpaisa_credit_debit_card_cko") {
			jQuery('body').trigger('update_checkout');
			jQuery('body').on('updated_checkout', function () {
				jQuery(".card-number").hide();
				jQuery(".card-frame").show();
				jQuery(".sp_cko_checkbox").show();
			})
		}
	});

});