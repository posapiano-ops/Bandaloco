'use strict';

(function ($) {
	$.redux = $.redux || {};

	$.redux.wbc_importer = function () {

		/**
		 * Global Variables
		 * 
		 * @author	imithemes
		 * @version	1.0.0
		 */
		var $importer_wrap,
			$import_btn,
			import_contents = 'no',
			import_attachments = 'no',
			import_widgets = 'no',
			import_theme_opts = 'no',
			import_sliders = 'no',
			page_builder = 'vc',
			allowImport = false,
			imported_demo = false,
			xhrPool = [],
			$lightbox_wrap = $('.imi-lightbox-wrap'),
			progressBarObject = {
				imported_count: 0,
				total_post: 180
			},
			nice;

		/**
		 * Open Lightbox (Import Button)
		 * 
		 * @author	imithemes
		 * @version	1.0.0
		 * @event	click
		 */
		$('.wrap-importer.theme.not-imported .wbc-importer-buttons .button, #wbc-importer-reimport').unbind('click').on('click', function (event) {
			event.preventDefault();

			var $this = $(this),
				$importer_wrap = $this.closest('.wrap-importer'),
				demo_id = $importer_wrap.data('demo-id');

			nice = $lightbox_wrap.find('.imi-lightbox[data-demo-id="' + demo_id + '"]').find('.imi-settings').niceScroll({
				scrollbarid: 'imi-lb-content',
				cursorwidth: "10px",
				cursorborder: "0",
				touchbehavior: true,
				autohidemode: false,
				background: "#e7e7e7",
				cursorcolor: "#64707d",
			});

			
			$lightbox_wrap.find('.imi-lightbox[data-demo-id="' + demo_id + '"]').find('.imi-settings').getNiceScroll().resize();

			$lightbox_wrap.find('.imi-lightbox[data-demo-id="' + demo_id + '"]').find('.imi-import-content-wrap').find('.imi-checkbox-wrap').find('input').each(function () {

				var $this = $(this),
					allowed = $this.attr('id').slice(0, -1);

				if ($this.is(':checked')) {

					if (allowed == 'all') {
						import_contents = import_attachments = import_sliders = import_theme_opts = import_widgets = 'yes';
					} else if (allowed == 'contents') {
						import_contents = 'yes';
					} else if (allowed == 'images') {
						import_attachments = 'yes';
					} else if (allowed == 'widgets') {
						import_widgets = 'yes';
					} else if (allowed == 'themeoptions') {
						import_theme_opts = 'yes';
					} else if (allowed == 'sliders') {
						import_sliders = 'yes';
					}

				} else {

					if (allowed == 'all') {
						import_contents = import_attachments = import_sliders = import_theme_opts = import_widgets = 'no';
					} else if (allowed == 'contents') {
						import_contents = 'no';
					} else if (allowed == 'images') {
						import_attachments = 'no';
					} else if (allowed == 'widgets') {
						import_widgets = 'no';
					} else if (allowed == 'themeoptions') {
						import_theme_opts = 'no';
					} else if (allowed == 'sliders') {
						import_sliders = 'no';
					}

				}

				if (import_contents == 'yes' || import_attachments == 'yes' || import_sliders == 'yes' || import_theme_opts == 'yes' || import_widgets == 'yes') {
					allowImport = true;
					$this.closest('.imi-lb-content').find('.imi-import-demo-btn').css({
						'opacity': '1',
						'cursor': 'pointer'
					});
				} else {
					allowImport = false;
					$this.closest('.imi-lb-content').find('.imi-import-demo-btn').css({
						'opacity': '0.6',
						'cursor': 'not-allowed'
					});
				}
			});

			$lightbox_wrap.find('.imi-lightbox[data-demo-id="' + demo_id + '"]').show().closest('.imi-lightbox-wrap').show();
		});

		/**
		 * Close Lightbox
		 * 
		 * @author	imithemes
		 * @version	1.0.0
		 * @event	click
		 */
		$lightbox_wrap.find('i.dashicons-no-alt').on('click', function (event) {
			event.preventDefault();
			$lightbox_wrap.hide();
			$('.imi-settings').getNiceScroll().remove();
		});

		

		/**
		 * Open Lightbox -> Choose Contents
		 * 
		 * @author	imithemes
		 * @version	1.0.0
		 * @event	click
		 */
		$('.imi-import-content-wrap').find('label').on('click', function () {
			var $this = $(this),
				allowed = $this.attr('for').replace(/\d+/g, ''),
				$input = $(this).siblings('input'),
				$other_inputs = $this.closest('.imi-checkbox-wrap').siblings('.imi-checkbox-wrap').find('input'),
				$all_status = $this.closest('.imi-checkbox-wrap').siblings('.imi-checkbox-wrap.imi-all-contents').find('input');

			if (!$input.is(':checked')) {
				if (allowed == 'all') {
					import_contents = import_attachments = import_sliders = import_theme_opts = import_widgets = 'yes';
					$other_inputs.attr('checked', true);
				} else {
					if ($other_inputs.not(':checked').length > 1) {
						$all_status.attr('checked', false);
					} else {
						$all_status.attr('checked', true);
					}

					if (allowed == 'contents') {
						import_contents = 'yes';
					} else if (allowed == 'images') {
						import_contents = import_attachments = 'yes';
						$this.closest('.imi-import-content-wrap').find('.imi-checkbox-input[value="contents"]').attr('checked', true);
					} else if (allowed == 'widgets') {
						import_widgets = 'yes';
					} else if (allowed == 'themeoptions') {
						import_theme_opts = 'yes';
					} else if (allowed == 'sliders') {
						import_sliders = 'yes';
					}
				}
			} else {
				if (allowed == 'all') {
					import_contents = import_attachments = import_sliders = import_theme_opts = import_widgets = 'no';
					$other_inputs.attr('checked', false);
				} else {
					$all_status.attr('checked', false);

					if (allowed == 'contents') {
						import_contents = 'no';
					} else if (allowed == 'images') {
						import_attachments = 'no';
					} else if (allowed == 'widgets') {
						import_widgets = 'no';
					} else if (allowed == 'themeoptions') {
						import_theme_opts = 'no';
					} else if (allowed == 'sliders') {
						import_sliders = 'no';
					}
				}
			}

			if (import_contents == 'yes' || import_attachments == 'yes' || import_sliders == 'yes' || import_theme_opts == 'yes' || import_widgets == 'yes') {
				allowImport = true;
				$this.closest('.imi-lb-content').find('.imi-import-demo-btn').css({
					'opacity': '1',
					'cursor': 'pointer'
				});
			} else {
				allowImport = false;
				$this.closest('.imi-lb-content').find('.imi-import-demo-btn').css({
					'opacity': '0.6',
					'cursor': 'not-allowed'
				});
			}
		});

		/**
		 * Open Lightbox -> Import Button
		 * 
		 * @author	imithemes
		 * @version	1.0.0
		 * @event	click
		 */
		$('.imi-import-demo-btn').unbind('click').on('click', function (e) {
			e.preventDefault();

			if (!allowImport) {
				return;
			}

			$import_btn = $(this);
			var demo_id = $import_btn.closest('.imi-lightbox').data('demo-id');

			// Close other lightboxes
			// $lightbox_wrap.find('i.dashicons-no-alt').trigger('click');			
			// Show current lightbox
			// $lightbox_wrap.find('.imi-lightbox[data-demo-id="' + demo_id + '"]').show().closest('.imi-lightbox-wrap').show();

			$importer_wrap = $('.wrap-importer[data-demo-id="' + demo_id + '"]');
			var message = 'By importing the demo content, items such as pages, posts, images, sliders, theme options, widgets and other configurations will be imported as well. it will take several minutes.\n\n Note: \'Some demos can take upto 15 minutes approximately so kindly wait until the process completes.';
			var max_progress_repeat = 24;

			if (xhrPool.length == 0) {
				var r = confirm(message);
				if (r == false) return;
			}

			$importer_wrap.find('.spinner').css('display', 'inline-block');
			$importer_wrap.removeClass('active imported');
			$importer_wrap.find('.importer-button').hide();

			var dir_demo_data = {};

			dir_demo_data.action = "imi_create_demo_dir";
			dir_demo_data.demo_import_id = $importer_wrap.attr("data-demo-id");
			dir_demo_data.nonce = $importer_wrap.attr("data-nonce");

			// Ajax Data (Import Data)
			var import_data = {};
			import_data.action = "redux_wbc_importer";
			import_data.demo_import_id = $importer_wrap.attr("data-demo-id");
			import_data.nonce = $importer_wrap.attr("data-nonce");
			import_data.type = 'import-demo-content';
			import_data.import_contents = import_contents;
			import_data.fetch_attachments = import_attachments;
			import_data.import_widgets = import_widgets;
			import_data.import_theme_opts = import_theme_opts;
			import_data.import_sliders = import_sliders;

			$importer_wrap.find('.wbc_image').css('opacity', '0.5');

			if (xhrPool.length > 0) {
				$.each(xhrPool, function (idx, jqXHR) {
					jqXHR.abort();
				});

				xhrPool.length = 0;
			}

			var $currentLightBox = $('.imi-lightbox[data-demo-id="' + demo_id + '"]');
			var $lightBoxWrap = $currentLightBox.closest('.imi-lightbox-wrap');
			var $importerStartProcess = $currentLightBox.find('.imi-start');

			imiImport(demo_id);
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: dir_demo_data,
				tryCount: 0,
				retryLimit: 3,
				beforeSend: function (jqXHR, settings) {
					xhrPool.push(jqXHR);
				}
			}).done(function (response) {
				xhrPool = [];
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: import_data,
					beforeSend: function (jqXHR, settings) {
						xhrPool.push(jqXHR);
					}
				}).done(function (response) {
					$importer_wrap.find('.wbc_image').css('opacity', '1');
					$importer_wrap.find('.spinner').css('display', 'none');

					if (response.length > 0 && response.match(/Have fun!/gi)) {
						$importer_wrap.addClass('rendered').find('.wbc-importer-buttons .importer-button').removeClass('import-demo-data');
						var reImportButton = '<div id="wbc-importer-reimport" class="wbc-importer-buttons button-primary import-demo-data importer-button">Re-Import</div>';
						$importer_wrap.find('.theme-actions .wbc-importer-buttons').append(reImportButton);
						$importer_wrap.find('.importer-button:not(#wbc-importer-reimport)').removeClass('button-primary').addClass('button').text('Imported').show();
						$importer_wrap.find('.importer-button').attr('style', '');
						$importer_wrap.addClass('imported active').removeClass('not-imported');
						imported_demo = true;
						// $importer_wrap.find('.wbc-progress-back').remove();
						// imiShowProgressBar(import_data, $importer_wrap, max_progress_repeat);
						$importer_wrap.find('#wbc-importer-reimport').hide();
						if (demo_id == 'wbc-import-2') {
							location.reload(false);
						} else {
							$lightBoxWrap.find('i.dashicons-no-alt').show();
							imiSuccessImportMessage(demo_id);
						}
					} else {
						$import_btn.trigger('click');
						console.log('ReImport');
					}
				}).fail(function () {
					$import_btn.trigger('click');
				});

				$importerStartProcess.find('.imi-downloading-data').hide();
				$importerStartProcess.prepend('<div class="wbc-progress-back"><div class="wbc-progress-bar button-primary"><span class="wbc-progress-count">0%</span></div>');
				imiShowProgressBar($importerStartProcess, $importer_wrap, max_progress_repeat);
			}).fail(function (xhr, textStatus, errorThrown) {
				if (textStatus == 'timeout') {
					this.tryCount++;
					if (this.tryCount <= this.retryLimit) {
						// try again
						$.ajax(this);
						return;
					}
					return;
				}
				if (xhr.status == 500) {
					// handle error
				} else {
					// handle error
				}
			});

			return false;
		});

		function imiShowProgressBar($importerStartProcess, $importer_wrap, max_progress_repeat) {
			if (imported_demo == false) {
				if (progressBarObject.imported_count != progressBarObject.total_post) {
					// console.log('Total: ' + progressBarObject.total_post);
					// console.log('Imported: ' + progressBarObject.imported_count);

					var percentage = Math.floor((progressBarObject.imported_count / progressBarObject.total_post) * 100);
					var current_percentage = $importerStartProcess.find('.wbc-progress-count').text().replace('%', '');

					if (percentage >= current_percentage) {
						percentage = (percentage > 0) ? percentage - 1 : percentage;
						$importerStartProcess.find('.wbc-progress-bar').css('width', percentage + "%");
						$importerStartProcess.find('.wbc-progress-count').text(percentage + "%");
					}

					if (percentage == current_percentage) {
						max_progress_repeat--;
					} else {
						max_progress_repeat = 20;
					}

					if (max_progress_repeat == 1) {
						console.log('Progress repeat: ' + max_progress_repeat);
						$import_btn.trigger('click');
					}

					progressBarObject.imported_count++;

					setTimeout(function () {
						imiShowProgressBar($importerStartProcess, $importer_wrap, max_progress_repeat);
					}, 1250);
				}
			} else {
				$importerStartProcess.find('.wbc-progress-back').remove();
			}
		}

		/**
		 * Stop all active ajax requests in jQuery
		 * 
		 * @author	imithemes
		 * @version	1.0.0
		 */
		// imiStopAllActiveAjax();

		/**
		 * Helper functions
		 * 
		 * @author	imithemes
		 * @version	1.0.0
		 */
		function imiStopAllActiveAjax() {
			$.xhrPool = [];

			$.xhrPool.abortAll = function () {
				_.each(this, function (jqXHR) {
					jqXHR.abort();
				});
			};

			$.ajaxSetup({
				beforeSend: function (jqXHR) {
					$.xhrPool.push(jqXHR);
				}
			});
		}

		function imiSuccessImportMessage(demo_id) {
			var $currentLightBox = $('.imi-lightbox[data-demo-id="' + demo_id + '"]');
			$currentLightBox.children('h2').hide();
			$currentLightBox.find('.imi-start').hide();
			$currentLightBox.children('.imi-suc-imp-title').fadeIn();
			$currentLightBox.find('.imi-suc-imp-t100').fadeIn();
			$currentLightBox.find('.imi-suc-imp-links').fadeIn();
		}

		function imiImport(demo_id) {
			var $currentLightBox = $('.imi-lightbox[data-demo-id="' + demo_id + '"]');
			var $lightBoxWrap = $currentLightBox.closest('.imi-lightbox-wrap');
			var $importerSettings = $currentLightBox.find('.imi-settings');
			var $importerStartWrap = $currentLightBox.find('.imi-suc-imp-content-wrap');
			var $importerStartProcess = $currentLightBox.find('.imi-start');
			var $niceScroll = $lightBoxWrap.find('.nicescroll-rails');

			$lightBoxWrap.find('i.dashicons-no-alt').hide();
			$importerSettings.hide();
			$niceScroll.hide();
			$importerStartWrap.fadeIn();

			$importerStartWrap.find('.imi-suc-imp-t100').hide().end().find('.imi-suc-imp-links').hide();
			$importerStartProcess.prepend('<span class="imi-downloading-data">Downloading Demo Data ...</span>');
		}
	};

	// Run Importer
	$(document).ready(function () {
		$.redux.wbc_importer();
	});
})(jQuery);