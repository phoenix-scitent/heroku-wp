function rawurldecode(str) {
	return decodeURIComponent((str + '').replace(/%(?![\da-f]{2})/gi, function() {
		// PHP tolerates poorly formed escape sequences
		return '%25';
	}));
}

jQuery(document).ready(function($) {
	var galleries = $('.ilightbox_gallery'),
		jetpackGalleries = $('.tiled-gallery'),
		nextGenGalleries = $('.ngg-galleryoverview');

	if (galleries.length) {
		galleries.each(function() {
			var t = $(this),
				kid = $('a[source]', t),
				options = t.data("options") && eval("(" + rawurldecode(t.data("options")) + ")") || {};
			kid.iLightBox(options);
		});
	}

	if (jetpackGalleries.length && ILIGHTBOX.jetPack) {
		jetpackGalleries.each(function() {
			var t = $(this),
				kid = $('a', t),
				options = ILIGHTBOX.options && eval("(" + rawurldecode(ILIGHTBOX.options) + ")") || {};
			options.attr = 'source';
			kid.each(function(i) {
				var $this = $(this),
					$img = $('img', $this),
					origFile = $img.data('orig-file');
				$this.attr('source', origFile);
			});
			kid.iLightBox(options);
		});
	}

	$(window).load(function() {
		if (nextGenGalleries.length && ILIGHTBOX.nextGEN) {
			nextGenGalleries.each(function() {
				var t = $(this),
					kid = $('.ngg-gallery-thumbnail a', t),
					options = ILIGHTBOX.options && eval("(" + rawurldecode(ILIGHTBOX.options) + ")") || {};
				kid.each(function(i) {
					var $this = $(this),
						title = $this.data('title'),
						description = $this.data('description');
					if (description.length > 0 || title.length > 0) {
						$this.data('caption', description.length > 0 ? description : title);
					}
					if (title.length > 0 && description.length === 0) {
						$this.data('title', null);
					}
					$this[0].onclick = null;
				});
				kid.iLightBox(options);
			});
		}
	});

	$(document).on('click', '.ilightbox_inline_gallery', function() {
		var t = $(this),
			slides = t.data("slides") && eval("(" + rawurldecode(t.data("slides")) + ")") || [];
		options = t.data("options") && eval("(" + rawurldecode(t.data("options")) + ")") || {};
		$.iLightBox(slides, options);
	});
});