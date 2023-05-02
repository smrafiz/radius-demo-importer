/**
 * Admin JS
 */

/* global rtdiAdminParams */

'use strict';

// Components
// import { rtsbAddToCart } from './components/add-to-cart';

(function ($) {
	// DOM Ready Event
	$(document).ready(() => {
		rtdiDemoImporter.init();
	});

	// Window Load Event
	$(window).on('load', () => {
		// rtsbFrontend.fixBuilderJumping();
	});

	// General Frontend Obj
	const rtdiDemoImporter = {
		init: () => {
			if ($('.rtdi-tab-filter').length > 0) {
				$('.rtdi-tab-group').each(function () {
					$(this).find('.rtdi-tab:first').addClass('rtdi-active');
				});

				// init Isotope
				var $grid = $('.rtdi-demo-box-wrap').imagesLoaded(function () {
					$grid.isotope({
						itemSelector: '.rtdi-demo-box',
					});
				});

				// store filter for each group
				var filters = {};

				$('.rtdi-tab-group').on('click', '.rtdi-tab', function (event) {
					var $button = $(event.currentTarget);
					// get group key
					var $buttonGroup = $button.parents('.rtdi-tab-group');
					var filterGroup = $buttonGroup.attr('data-filter-group');
					// set filter for group
					filters[filterGroup] = $button.attr('data-filter');
					// combine filters
					var filterValue = concatValues(filters);
					// set filter for Isotope
					$grid.isotope({filter: filterValue});
				});

				// change is-checked class on buttons
				$('.rtdi-tab-group').each(function (i, buttonGroup) {
					var $buttonGroup = $(buttonGroup);
					$buttonGroup.on('click', '.rtdi-tab', function (event) {
						$buttonGroup.find('.rtdi-active').removeClass('rtdi-active');
						var $button = $(event.currentTarget);
						$button.addClass('rtdi-active');
					});
				});

				// flatten object by concatting values
				function concatValues(obj) {
					var value = '';
					for (var prop in obj) {
						value += obj[prop];
					}
					return value;
				}
			}

			$('.rtdi-modal-button').on('click', function (e) {
				e.preventDefault();
				$('body').addClass('rtdi-modal-opened');
				var modalId = $(this).attr('href');
				$(modalId).fadeIn();

				$("html, body").animate({scrollTop: 0}, "slow");
			});

			$('.rtdi-modal-back, .rtdi-modal-cancel').on('click', function (e) {
				$('body').removeClass('rtdi-modal-opened');
				$('.rtdi-modal').hide();
				$("html, body").animate({scrollTop: 0}, "slow");
			});

			$('body').on('click', '.rtdi-import-demo', function () {
				var $el = $(this);
				var demo = $(this).attr('data-demo-slug');
				var reset = $('#checkbox-reset-' + demo).is(':checked');
				var excludeImages = $('#checkbox-exclude-image-' + demo).is(':checked');
				var resetMessage = '';

				if (reset) {
					resetMessage = rtdiAdminParams.resetDatabase;
					var confirmMessage = 'Are you sure to proceed? Resetting the database will delete all your contents.';
				} else {
					var confirmMessage = 'Are you sure to proceed?';
				}

				var $importTrue = confirm(confirmMessage);

				if ($importTrue == false) {
					return;
				}

				$("html, body").animate({scrollTop: 0}, "slow");

				$('#rtdi-modal-' + demo).hide();
				$('#rtdi-import-progress').show();

				$('#rtdi-import-progress .rtdi-import-progress-message').html(rtdiAdminParams.prepareImporting).fadeIn();

				var info = {
					demo: demo,
					reset: reset,
					nextPhase: 'rtdi_install_demo',
					excludeImages: excludeImages,
					nextPhaseMessage: resetMessage
				};

				setTimeout(function () {
					do_ajax(info);
				}, 2000);
			});

			function do_ajax(info) {
				console.log(info);
				if (info.nextPhase) {
					var data = {
						action: info.nextPhase,
						demo: info.demo,
						reset: info.reset,
						excludeImages: info.excludeImages,
						__rtdi_wpnonce: rtdiAdminParams.__rtdi_wpnonce,
					};

					jQuery.ajax({
						url: ajaxurl,
						type: 'post',
						data: data,
						beforeSend: function () {
							if (info.nextPhaseMessage) {
								$('#rtdi-import-progress .rtdi-import-progress-message').hide().html('').fadeIn().html(info.nextPhaseMessage);
							}
						},
						success: function (response) {
							var info = JSON.parse(response);

							if (!info.error) {
								if (info.completedMessage) {
									$('#rtdi-import-progress .rtdi-import-progress-message').hide().html('').fadeIn().html(info.completedMessage);
								}
								setTimeout(function () {
									do_ajax(info);
								}, 2000);
							} else {
								$('#rtdi-import-progress .rtdi-import-progress-message').html(info.errorMessage);
								$('#rtdi-import-progress').addClass('import-error');

							}
						},
						error: function (xhr, status, error) {
							console.log(error)
							var errorMessage = xhr.status + ': ' + xhr.statusText
							$('#rtdi-import-progress .rtdi-import-progress-message').html(rtdiAdminParams.importError);
							$('#rtdi-import-progress').addClass('import-error');
						}
					});
				} else {
					$('#rtdi-import-progress .rtdi-import-progress-message').html(rtdiAdminParams.importSuccess);
					$('#rtdi-import-progress').addClass('import-success');
				}
			}
		},
	};
})(jQuery);
