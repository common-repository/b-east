jQuery(function ($) {
	function askForMore(e) {
		if (e.target.value !== "beast_options") {
			return;
		}

		var $form = $(this).parents("#posts-filter, #wc-orders-filter");

		// Get selected order id's.
		var post_ids = [];
		$('input[name="id[]"]:checked, input[name="post[]"]:checked').each(() =>
			post_ids.push($(this).val())
		);

		if (post_ids.length < 1) {
			alert("Please select at least one post.");

			return;
		}

		tb_show("B-east", "?TB_inline=true&inlineId=beast-options-modal");

		$("#TB_window")
			.find("button[type=submit]")
			.on("click", function (e) {
				e.preventDefault();

				var $beast_fields = $(
					'<div id="beast-fields" style="display:none;"></div>'
				);

				$("#TB_window")
					.find("input, select")
					.each(function () {
						$beast_fields.append(
							$(
								'<input type="hidden" name="' +
									$(this).attr("name") +
									'" value="' +
									$(this).val() +
									'">'
							)
						);
					});

				$form.append($beast_fields);
				$form.submit();
			});
	}

	function init() {
		$("#bulk-action-selector-top, #bulk-action-selector-bottom").on(
			"click",
			askForMore
		);
	}

	init();
});
