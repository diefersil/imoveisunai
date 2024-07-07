/**
 * This script enhances the functionality of a Favethemes header, footer builder.
 * It relies on jQuery for DOM manipulation and event handling.
 * 
 * Functions are organized to handle specific parts of the page interaction:
 * - Initialization of dynamic select elements
 * - Visibility control of certain UI elements
 * - Handling user interactions like clicks and changes in the form
 * 
 * Error handling and optimization have been considered in the function implementations.
 */
(function( $ ) {
	'use strict';

	// Throttle function to limit the rate at which a function can fire.
	function throttle(func, limit) {
	    let lastFunc;
	    let lastRan;
	    return function() {
	        const context = this;
	        const args = arguments;
	        if (!lastRan) {
	            func.apply(context, args);
	            lastRan = Date.now();
	        } else {
	            clearTimeout(lastFunc);
	            lastFunc = setTimeout(function() {
	                if ((Date.now() - lastRan) >= limit) {
	                    func.apply(context, args);
	                    lastRan = Date.now();
	                }
	            }, limit - (Date.now() - lastRan));
	        }
	    }
	}

	// Debounce function to postpone a function's execution until after a delay.
	function debounce(func, delay) {
	    let debounceTimer;
	    return function() {
	        const context = this;
	        const args = arguments;
	        clearTimeout(debounceTimer);
	        debounceTimer = setTimeout(() => func.apply(context, args), delay);
	    };
	}

	// Initializes a dynamic select element using Select2 with AJAX.
	const initializeDynamicSelect = (selector) => {
	    const ajaxOptions = {
	        url: ajaxurl,
	        dataType: 'json',
	        method: 'post',
	        delay: 250,
	        data: (params) => ({
	            q: params.term, // search term
	            page: params.page,
	            action: 'fts_retrieve_posts_based_on_query',
	            nonce: admin.nonce
	        }),
	        processResults: (data) => ({
	            results: data
	        }),
	        cache: true
	    };

	    const select2Options = {
	        placeholder: admin.search,
	        ajax: ajaxOptions,
	        minimumInputLength: 2,
	        language: admin.language
	    };

	    try {
	        $(selector).select2(select2Options);
	    } catch (error) {
	        console.error('Error initializing dynamic select:', error);
	    }
	};

	// Updates the visibility of the close button based on certain conditions.
	const updateCloseButtonVisibility = (wrap) => {
	    const dataType = wrap.closest('.fts-fields_container').attr('data-type');
	    const rules = wrap.find('.fts-rule_condition_block');
	    let shouldShowClose = dataType === 'display' ? rules.length > 1 : true;

	    rules.each((index, rule) => {
	        const deleteIcon = $(rule).find('.fts-delete_rule_icon');
	        shouldShowClose ? deleteIcon.removeClass('hidden') : deleteIcon.addClass('hidden');
	    });
	};


	// Handles page-specific logic when the document is ready.
	$(document).ready(function($) {

		const updateFieldVisibility = () => {
		    const selectedTemplateType = $('#fts_template_type').val() || 'none';
		    const optionsTable = $('.houzez-fts-options-table');
		    const customBlockRow = $('.fts-row.fts-shortcode-row');

		    // Handle visibility of the options table
		    if (['none', 'tmp_custom_block', 'tmp_megamenu'].includes(selectedTemplateType)) {
		        optionsTable.addClass('fts-options-none');
		    } else {
		        optionsTable.removeClass('fts-options-none');
		    }

		    // Handle visibility of the custom block row
		    if (selectedTemplateType === 'tmp_custom_block') {
		        customBlockRow.show();
		    } else {
		        customBlockRow.hide();
		    }
		};

		// Attach event handler for changes in template type selection.
		$(document).on('change', '#fts_template_type', () => {
		    updateFieldVisibility();
		});

		// Initialize field visibility on document load.
		updateFieldVisibility();

		jQuery('.fts-rule_condition_block').each((index, element) => {
		    const ruleBlock = jQuery(element);
		    const condition = ruleBlock.find('select.fts-selection_dropdown');
		    const conditionValue = condition.val();
		    const specificPageWrap = ruleBlock.next('.fts-targeted-page-wrap');

		    if (conditionValue === 'specifics') {
		        specificPageWrap.slideDown(300);
		    }
		});

		// Initialize select elements with dynamic content.
		jQuery('select.fts-targeted-select2').each((index, element) => {
		    initializeDynamicSelect(element);
		});

		// Update the visibility of close buttons in selector containers.
		jQuery('.fts-selector_container').each((index, container) => {
		    updateCloseButtonVisibility(jQuery(container));
		});

		// Update the exclusion button visibility based on certain conditions.
	    const updateExclusionButtonVisibility = (forceShow = false, forceHide = false) => {
		    const displayOnWrap = $('.fts-display-on-wrap');
		    const excludeOnWrap = $('.fts-exclude-on-wrap');
		    const excludeFieldWrap = excludeOnWrap.closest('tr');
		    const addExcludeBlock = displayOnWrap.find('.fts-create_exclusion_rule');
		    const excludeConditions = excludeOnWrap.find('.fts-rule_condition_block');

		    if (forceHide) {
		        excludeFieldWrap.addClass('hidden');
		        addExcludeBlock.removeClass('hidden');
		    } else if (forceShow) {
		        excludeFieldWrap.removeClass('hidden');
		        addExcludeBlock.addClass('hidden');
		    } else {
		        const isSingleEmptyCondition = excludeConditions.length === 1 && 
		                                        $(excludeConditions[0]).find('select.fts-selection_dropdown').val() === '';
		        if (isSingleEmptyCondition) {
		            excludeFieldWrap.addClass('hidden');
		            addExcludeBlock.removeClass('hidden');
		        } else {
		            excludeFieldWrap.removeClass('hidden');
		            addExcludeBlock.addClass('hidden');
		        }
		    }
		};
		updateExclusionButtonVisibility();

		// Update the target rule input based on user selection.
		const updateTargetRuleInput = (wrapper) => {
		    let newValues = [];

		    wrapper.find('.fts-rule_condition_block').each((index, element) => {
		        const ruleCondition = $(element).find('select.fts-selection_dropdown');
		        const specificPage = $(element).find('select.fts-targeted-page');
		        const ruleConditionValue = ruleCondition.val();
		        const specificPageValue = specificPage.val();

		        if (ruleConditionValue !== '') {
		            newValues.push({
		                type: ruleConditionValue,
		                specific: specificPageValue
		            });
		        }
		    });
		};

		jQuery(document).on('change', '.fts-rule_condition_block select.fts-selection_dropdown', (event) => {
		    const selectedDropdown = jQuery(event.currentTarget);
		    const selectedValue = selectedDropdown.val();
		    const fieldContainer = selectedDropdown.closest('.fts-fields_container');
		    const targetedPageWrap = selectedDropdown.closest('.fts-rule_condition_block').next('.fts-targeted-page-wrap');

		    if (selectedValue === 'specifics') {
		        targetedPageWrap.slideDown(300);
		    } else {
		        targetedPageWrap.slideUp(300);
		    }

		    updateTargetRuleInput(fieldContainer);
		});

		jQuery('.fts-selector_container').on('change', '.fts-targeted-select2', (event) => {
		    const selectedElement = jQuery(event.currentTarget);
		    const fieldContainer = selectedElement.closest('.fts-fields_container');

		    updateTargetRuleInput(fieldContainer);
		});

		jQuery('.fts-selector_container').on('click', '.fts-delete_rule_icon', (event) => {
		    const clickedIcon = jQuery(event.currentTarget);
		    const ruleConditionBlock = clickedIcon.closest('.fts-rule_condition_block');
		    const fieldContainer = clickedIcon.closest('.fts-fields_container');
		    const dataType = fieldContainer.attr('data-type');

		    if (dataType === 'exclude') {
		        if (fieldContainer.find('.fts-selection_dropdown').length === 1) {
		            const dropdown = fieldContainer.find('.fts-selection_dropdown');
		            dropdown.val('').trigger('change');
		            fieldContainer.find('.fts-targeted-page').val('');
		            updateExclusionButtonVisibility(false, true);
		        } else {
		            removeRuleBlock(clickedIcon);
		        }
		    } else {
		        removeRuleBlock(clickedIcon);
		    }

		    let ruleCount = 0;
		    fieldContainer.find('.fts-rule_condition_block').each((index, element) => {
		        const condition = jQuery(element);
		        updateRuleAttributes(condition, index);
		        ruleCount = index;
		    });

		    fieldContainer.find('.fts-create_new_rule a').attr('data-rule-id', ruleCount);

		    updateCloseButtonVisibility(fieldContainer);
		    updateTargetRuleInput(fieldContainer);
		});

		function removeRuleBlock(clickedIcon) {
		    clickedIcon.parent('.fts-rule_condition_block').next('.fts-targeted-page-wrap').remove();
		    clickedIcon.closest('.fts-rule_condition_block').remove();
		}

		function updateRuleAttributes(condition, index) {
		    const selectDropdown = condition.find('.fts-selection_dropdown');
		    const selectSpecific = condition.find('.fts-targeted-page');
		    const oldRuleId = condition.attr('data-rule');
		    const locationName = selectDropdown.attr('name');

		    condition.attr('data-rule', index);
		    selectDropdown.attr('name', locationName.replace(`[${oldRuleId}]`, `[${index}]`));
		    condition.removeClass(`fts-rule-${oldRuleId}`).addClass(`fts-rule-${index}`);
		}

		jQuery('.fts-selector_container').on('click', '.fts-create_new_rule a', (event) => {
		    event.preventDefault();
		    event.stopPropagation();

		    const clickedElement = jQuery(event.currentTarget);
		    const ruleId = parseInt(clickedElement.attr('data-rule-id'), 10);
		    const newRuleId = ruleId + 1;
		    const ruleType = clickedElement.attr('data-rule-type');
		    const ruleWrap = clickedElement.closest('.fts-selector_container').find('.fts-fields_builder_wrap');
		    const ruleTemplate = wp.template(`fts-${ruleType}-condition`);
		    const fieldContainer = clickedElement.closest('.fts-fields_container');

		    ruleWrap.append(ruleTemplate({ id: newRuleId, type: ruleType }));

		    initializeDynamicSelect(`.fts-${ruleType}-on .fts-targeted-select2`);

		    clickedElement.attr('data-rule-id', newRuleId);

		    updateCloseButtonVisibility(fieldContainer);
		});

		jQuery('.fts-selector_container').on('click', '.fts-create_exclusion_rule a', (event) => {
		    event.preventDefault();
		    event.stopPropagation();
		    updateExclusionButtonVisibility(true);
		});

		
	});


})( jQuery );
