(function ($) {
	'use strict';

	$(document).ready(function () {
		const $inputElement = $('#ing_ksiegowosc_api_v');

		$inputElement.on('keydown', function (e) {
			if (/\s/.test(String.fromCharCode(e.keyCode || e.which))) {
				e.preventDefault();
			}
		});

		$inputElement.on('paste', function (e) {
			e.preventDefault();

			$(this).val(e.originalEvent.clipboardData.getData('Text').replace(/\s+/g, ''));
		});
	});

})(jQuery);
