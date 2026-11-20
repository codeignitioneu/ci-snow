/* Seasonal Snow – settings page: color pickers and media library picker. */
jQuery(function ($) {
	$('.ci-snow-color').wpColorPicker();

	var $ids = $('#ci-snow-image-ids');
	var $list = $('#ci-snow-images');
	var frame;

	$('#ci-snow-add-images').on('click', function (e) {
		e.preventDefault();
		if (frame) { frame.open(); return; }
		frame = wp.media({
			title: 'Seasonal Snow',
			button: { text: 'Use these images' },
			library: { type: 'image' },
			multiple: true
		});
		frame.on('select', function () {
			var current = $ids.val() ? $ids.val().split(',') : [];
			frame.state().get('selection').each(function (att) {
				var a = att.toJSON();
				if (current.indexOf(String(a.id)) === -1) {
					current.push(String(a.id));
					var url = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
					$list.append('<span class="ci-snow-thumb" data-id="' + a.id + '"><img src="' + url + '" width="48" height="48" alt=""></span>');
				}
			});
			$ids.val(current.slice(0, 20).join(','));
		});
		frame.open();
	});

	$('#ci-snow-clear-images').on('click', function (e) {
		e.preventDefault();
		$ids.val('');
		$list.empty();
	});
});
