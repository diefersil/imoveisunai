'use strict';

(($) => {
    if (
        typeof elementor === 'undefined' ||
        typeof elementorCommon === 'undefined'
    ) {
        return;
    }

    elementor.on('preview:loaded', () => {
        let dialog = null;
        //Houzez Template button
        let buttons = $('#tmpl-elementor-add-section');

        const text = buttons
            .text()
            .replace(
                '<div class="elementor-add-section-drag-title',
                '<div class="elementor-add-section-area-button houzez-library-modal-btn" title="Houzez Templates">Houzez Templates</div><div class="elementor-add-section-drag-title'
            );

        buttons.text(text);

        // Call modal.
        $(elementor.$previewContents[0].body).on(
            'click',
            '.houzez-library-modal-btn',
            () => {
                if (dialog) {
                    dialog.show();
                    return;
                }

                var modalOptions = {
                    id: 'houzez-library-modal',
                    headerMessage: $(
                        '#tmpl-elementor-houzez-library-modal-header'
                    ).html(),
                    message: $('#tmpl-elementor-houzez-library-modal').html(),
                    className: 'elementor-templates-modal',
                    closeButton: true,
                    draggable: false,
                    hide: {
                        onOutsideClick: true,
                        onEscKeyPress: true,
                    },
                    position: {
                        my: 'center',
                        at: 'center',
                    },
                };
                dialog = elementorCommon.dialogsManager.createWidget(
                    'lightbox',
                    modalOptions
                );
                dialog.show();

                loadTemplates();
            }
        );

        // Progressive loading variables
        let progressiveLoading = {
            isLoading: false,
            currentBatch: 0,
            totalBatches: 0,
            totalTemplates: 0,
            loadedTemplates: 0,
            batchSize: 50, // Show 50 templates at a time for optimal UX
            allTemplates: [],
            loadingSource: null, // Track if loading from 'local', 'remote', or 'sync'
        };

        // Debug logging function
        function logProgressiveLoading(message, data = {}) {
            if (typeof console !== 'undefined') {
                console.log(`📚 Houzez Library: ${message}`, {
                    source: progressiveLoading.loadingSource,
                    batch: progressiveLoading.currentBatch + 1,
                    totalBatches: progressiveLoading.totalBatches,
                    loaded: progressiveLoading.loadedTemplates,
                    total: progressiveLoading.totalTemplates,
                    ...data,
                });
            }
        }

        // Load items with progressive loading for better UX
        function loadTemplates() {
            showLoader();
            progressiveLoading.isLoading = true;
            progressiveLoading.allTemplates = [];
            progressiveLoading.loadingSource = 'local';

            logProgressiveLoading('Starting template loading process');

            // First try progressive loading from local storage
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'houzez_get_templates_progressive',
                    nonce: houzez_library_ajax.nonce,
                },
                success: function (response) {
                    if (
                        response.success &&
                        response.data &&
                        response.data.elements &&
                        response.data.batch_info
                    ) {
                        // Initialize progressive loading with first batch
                        const data = response.data;
                        progressiveLoading.totalBatches =
                            data.batch_info.total_batches;
                        progressiveLoading.totalTemplates =
                            data.batch_info.total_templates;
                        progressiveLoading.loadedTemplates =
                            data.batch_info.loaded_so_far;
                        progressiveLoading.currentBatch =
                            data.batch_info.current_batch;

                        // Check if this is a remote_all response (no local templates)
                        if (data.batch_info.source === 'remote_all') {
                            logProgressiveLoading(
                                'Remote templates loaded, starting cached progressive display'
                            );

                            // Store all templates for progressive display
                            progressiveLoading.allTemplates =
                                data.batch_info.all_templates || data.elements;

                            // Display first batch immediately
                            displayTemplatesProgressive(data, true);
                            hideLoader();

                            // Load remaining batches from cached templates
                            if (
                                data.batch_info.has_more &&
                                data.batch_info.all_templates
                            ) {
                                loadRemoteRemainingBatches(1);
                            } else {
                                progressiveLoading.isLoading = false;
                                console.log(
                                    `🎯 Remote cached progressive loading complete: ${progressiveLoading.loadedTemplates} ${houzez_library_ajax.progressive_loading.templates_loaded}`
                                );
                            }
                        } else {
                            // Local templates found
                            logProgressiveLoading(
                                'Local templates found, starting progressive display'
                            );

                            // Display first batch immediately
                            displayTemplatesProgressive(data, true);
                            hideLoader();

                            // Load remaining batches in background if there are more
                            // Always load remaining batches progressively, even if locally synced
                            if (data.batch_info.has_more) {
                                loadRemainingBatches(
                                    data.batch_info.next_batch
                                );
                            } else {
                                progressiveLoading.isLoading = false;
                                console.log(
                                    `🎯 Local progressive loading complete: ${progressiveLoading.loadedTemplates} ${houzez_library_ajax.progressive_loading.templates_loaded}`
                                );
                            }
                        }
                    } else {
                        // Fallback to remote if no local templates
                        progressiveLoading.loadingSource = 'remote';
                        logProgressiveLoading(
                            'No local templates found, falling back to remote'
                        );
                        loadRemoteTemplatesProgressive();
                    }
                },
                error: function () {
                    // Fallback to remote on error
                    loadRemoteTemplatesProgressive();
                },
            });
        }

        // Load remaining batches in background
        function loadRemainingBatches(nextBatchIndex) {
            if (!progressiveLoading.isLoading || nextBatchIndex === null) {
                return;
            }

            // Add small delay to not overwhelm the server
            setTimeout(() => {
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'houzez_get_templates_batch',
                        nonce: houzez_library_ajax.nonce,
                        batch_index: nextBatchIndex,
                        batch_size: progressiveLoading.batchSize,
                    },
                    success: function (response) {
                        if (
                            response.success &&
                            response.data &&
                            response.data.elements &&
                            response.data.batch_info
                        ) {
                            const data = response.data;

                            // Update progress
                            progressiveLoading.loadedTemplates =
                                data.batch_info.loaded_so_far;
                            progressiveLoading.currentBatch =
                                data.batch_info.current_batch;

                            // Add new templates to display
                            displayTemplatesProgressive(data, false);

                            // Update loading indicator
                            updateLoadingProgress();

                            // Continue with next batch if available
                            if (
                                data.batch_info.has_more &&
                                data.batch_info.next_batch !== null
                            ) {
                                loadRemainingBatches(
                                    data.batch_info.next_batch
                                );
                            } else {
                                // All batches loaded
                                progressiveLoading.isLoading = false;
                                hideLoadingProgress();
                                console.log(
                                    `🚀 Progressive loading complete: ${progressiveLoading.loadedTemplates} ${houzez_library_ajax.progressive_loading.templates_loaded}`
                                );
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.warn(
                            `Failed to load batch ${nextBatchIndex}:`,
                            error
                        );
                        // Try to continue with next batch despite error
                        if (
                            nextBatchIndex + 1 <
                            progressiveLoading.totalBatches
                        ) {
                            loadRemainingBatches(nextBatchIndex + 1);
                        } else {
                            progressiveLoading.isLoading = false;
                            hideLoadingProgress();
                        }
                    },
                });
            }, 150); // 150ms delay between batches for smoother UX
        }

        // Fallback: Progressive loading from remote API - get ALL templates first
        function loadRemoteTemplatesProgressive() {
            // First, get all templates using the all=true parameter
            $.ajax({
                url: 'https://studio.houzez.co/wp-json/favethemes-blocks/v1/templates?all=true',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (
                        response &&
                        response.elements &&
                        Array.isArray(response.elements)
                    ) {
                        // Process all templates and create batches for progressive display
                        const allTemplates = response.elements;
                        const batchSize = 50;
                        const totalTemplates = allTemplates.length;
                        const totalBatches = Math.ceil(
                            totalTemplates / batchSize
                        );

                        // Set up progressive loading state
                        progressiveLoading.totalBatches = totalBatches;
                        progressiveLoading.totalTemplates = totalTemplates;
                        progressiveLoading.allTemplates = allTemplates;

                        // Show first batch immediately
                        const firstBatch = allTemplates.slice(0, batchSize);
                        const firstBatchData = {
                            elements: firstBatch,
                            tags: response.tags || [],
                            batch_info: {
                                current_batch: 0,
                                total_batches: totalBatches,
                                total_templates: totalTemplates,
                                has_more: totalBatches > 1,
                                next_batch: totalBatches > 1 ? 1 : null,
                                loaded_so_far: firstBatch.length,
                                source: 'remote_all',
                            },
                        };

                        progressiveLoading.loadedTemplates = firstBatch.length;
                        progressiveLoading.currentBatch = 0;

                        displayTemplatesProgressive(firstBatchData, true);
                        hideLoader();

                        // Load remaining batches from the cached all templates
                        if (totalBatches > 1) {
                            loadRemoteRemainingBatches(1);
                        } else {
                            progressiveLoading.isLoading = false;
                        }
                    } else {
                        // Final fallback to bulk endpoint
                        loadRemoteTemplatesBulk();
                    }
                },
                error: function () {
                    loadRemoteTemplatesBulk();
                },
            });
        }

        // Load remaining batches from cached templates (no API calls)
        function loadRemoteRemainingBatches(batchIndex) {
            if (
                !progressiveLoading.isLoading ||
                !progressiveLoading.allTemplates
            ) {
                return;
            }

            // Add small delay to show progressive loading effect
            setTimeout(() => {
                const batchSize = 50;
                const allTemplates = progressiveLoading.allTemplates;
                const startIndex = batchIndex * batchSize;
                const endIndex = startIndex + batchSize;
                const batch = allTemplates.slice(startIndex, endIndex);

                if (batch.length > 0) {
                    const batchData = {
                        elements: batch,
                        tags: [], // Don't duplicate tags
                        batch_info: {
                            current_batch: batchIndex,
                            total_batches: progressiveLoading.totalBatches,
                            total_templates: progressiveLoading.totalTemplates,
                            has_more:
                                batchIndex + 1 <
                                progressiveLoading.totalBatches,
                            next_batch:
                                batchIndex + 1 < progressiveLoading.totalBatches
                                    ? batchIndex + 1
                                    : null,
                            loaded_so_far: Math.min(
                                endIndex,
                                progressiveLoading.totalTemplates
                            ),
                            source: 'remote_cached',
                        },
                    };

                    progressiveLoading.loadedTemplates =
                        batchData.batch_info.loaded_so_far;
                    progressiveLoading.currentBatch = batchIndex;

                    displayTemplatesProgressive(batchData, false);
                    updateLoadingProgress();

                    // Continue with next batch if available
                    if (batchData.batch_info.has_more) {
                        loadRemoteRemainingBatches(batchIndex + 1);
                    } else {
                        progressiveLoading.isLoading = false;
                        hideLoadingProgress();
                        console.log(
                            `🚀 Progressive loading complete: ${progressiveLoading.loadedTemplates} ${houzez_library_ajax.progressive_loading.templates_loaded}`
                        );
                    }
                }
            }, 100); // 100ms delay for smooth progressive loading
        }

        // Load remaining pages from remote API
        function loadRemoteRemainingPages(page) {
            if (!progressiveLoading.isLoading) return;

            setTimeout(() => {
                $.ajax({
                    url: `https://studio.houzez.co/wp-json/favethemes-blocks/v1/templates?page=${page}&per_page=50`,
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response && response.elements) {
                            const batchData = {
                                elements: response.elements,
                                tags: response.tags || [],
                                batch_info: {
                                    current_batch: page - 1,
                                    total_batches: response.pagination
                                        ? response.pagination.total_pages
                                        : progressiveLoading.totalBatches,
                                    total_templates:
                                        response.total_records ||
                                        progressiveLoading.totalTemplates,
                                    has_more: response.pagination
                                        ? response.pagination.has_next
                                        : false,
                                    loaded_so_far:
                                        progressiveLoading.loadedTemplates +
                                        response.elements.length,
                                    source: 'remote_paginated',
                                },
                            };

                            progressiveLoading.loadedTemplates =
                                batchData.batch_info.loaded_so_far;
                            displayTemplatesProgressive(batchData, false);
                            updateLoadingProgress();

                            if (batchData.batch_info.has_more) {
                                loadRemoteRemainingPages(page + 1);
                            } else {
                                progressiveLoading.isLoading = false;
                                hideLoadingProgress();
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.warn(
                            `Failed to load remote page ${page}:`,
                            error
                        );
                        progressiveLoading.isLoading = false;
                        hideLoadingProgress();
                    },
                });
            }, 300); // 300ms delay for remote API
        }

        // Final fallback: Bulk endpoint (legacy) - use all=true to get all templates
        function loadRemoteTemplatesBulk() {
            $.ajax({
                url: 'https://studio.houzez.co/wp-json/favethemes-blocks/v1/templates?all=true',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response && response.elements) {
                        displayTemplates(response);
                        hideLoader();
                    } else {
                        showError(
                            "The library can't be loaded from the server."
                        );
                        hideLoader();
                    }
                },
                error: function () {
                    showError("The library can't be loaded from the server.");
                    hideLoader();
                },
            });
        }

        // Display templates with progressive loading support
        function displayTemplatesProgressive(response, isFirstBatch = true) {
            console.log(
                `📦 Loading batch: ${response.batch_info.current_batch + 1}/${
                    response.batch_info.total_batches
                } (${response.elements.length} templates)`
            );

            // Validate response structure
            if (
                !response ||
                !response.elements ||
                !Array.isArray(response.elements)
            ) {
                console.error('Invalid template response structure', response);
                if (isFirstBatch) {
                    showError('Invalid template data structure.');
                    hideLoader();
                }
                return;
            }

            // Add templates to our master list
            progressiveLoading.allTemplates =
                progressiveLoading.allTemplates.concat(response.elements);

            if (isFirstBatch) {
                // First batch: clear container and set up filters
                var itemTemplate = wp.template(
                    'elementor-houzez-library-modal-item'
                );
                var itemOrderTemplate = wp.template(
                    'elementor-houzez-library-modal-order'
                );

                $(
                    '#houzez-library-modal #elementor-template-library-templates-container'
                ).empty();
                $(
                    '#houzez-library-modal #elementor-template-library-filter-toolbar-remote'
                ).empty();

                // Create response with first batch for templates
                const firstBatchResponse = {
                    elements: response.elements,
                    tags: response.tags || [],
                };

                $(itemTemplate(firstBatchResponse)).appendTo(
                    $(
                        '#houzez-library-modal #elementor-template-library-templates-container'
                    )
                );
                $(itemOrderTemplate(firstBatchResponse)).appendTo(
                    $(
                        '#houzez-library-modal #elementor-template-library-filter-toolbar-remote'
                    )
                );

                // Set up functionality for first batch
                importTemplate();
                setupSyncButton();

                // Add loading progress indicator
                if (response.batch_info.has_more) {
                    showLoadingProgress();
                }
            } else {
                // Subsequent batches: append new templates
                var itemTemplate = wp.template(
                    'elementor-houzez-library-modal-item'
                );
                const batchResponse = {
                    elements: response.elements,
                    tags: [], // Don't duplicate tags
                };

                $(itemTemplate(batchResponse)).appendTo(
                    $(
                        '#houzez-library-modal #elementor-template-library-templates-container'
                    )
                );

                // Re-bind import functionality for new templates
                bindImportForNewTemplates();
            }
        }

        // Show loading progress indicator
        function showLoadingProgress() {
            const progressHtml = `
                <div id="houzez-progressive-loader" class="houzez-progressive-loading">
                    <div class="houzez-progress-bar">
                        <div class="houzez-progress-fill" style="width: 0%;"></div>
                    </div>
                                         <div class="houzez-progress-text">
                         <span id="houzez-progress-message">${houzez_library_ajax.progressive_loading.loading_more}</span>
                         <span id="houzez-progress-count">0 / 0</span>
                     </div>
                </div>
            `;

            $(progressHtml).appendTo(
                $(
                    '#houzez-library-modal #elementor-template-library-templates-container'
                )
            );

            // Add CSS for progress indicator
            if (!$('#houzez-progressive-styles').length) {
                $(`
                    <style id="houzez-progressive-styles">
                        .houzez-progressive-loading {
                            position: sticky;
                            bottom: 0;
                            background: rgba(255, 255, 255, 0.95);
                            padding: 15px;
                            border-top: 1px solid #e0e0e0;
                            backdrop-filter: blur(5px);
                            z-index: 1000;
                        }
                        .houzez-progress-bar {
                            height: 6px;
                            background: #f0f0f0;
                            border-radius: 3px;
                            overflow: hidden;
                            margin-bottom: 8px;
                        }
                        .houzez-progress-fill {
                            height: 100%;
                            background: linear-gradient(90deg, #35AAE1, #2196F3);
                            border-radius: 3px;
                            transition: width 0.3s ease;
                        }
                        .houzez-progress-text {
                            display: flex;
                            justify-content: space-between;
                            font-size: 12px;
                            color: #666;
                        }
                    </style>
                `).appendTo('head');
            }
        }

        // Update loading progress
        function updateLoadingProgress() {
            const percentage = Math.round(
                (progressiveLoading.loadedTemplates /
                    progressiveLoading.totalTemplates) *
                    100
            );

            $('#houzez-progressive-loader .houzez-progress-fill').css(
                'width',
                percentage + '%'
            );
            $('#houzez-progress-message').text(
                `${houzez_library_ajax.progressive_loading.loading_batch} ${
                    progressiveLoading.currentBatch + 1
                }/${progressiveLoading.totalBatches}...`
            );
            $('#houzez-progress-count').text(
                `${progressiveLoading.loadedTemplates} / ${progressiveLoading.totalTemplates}`
            );
        }

        // Hide loading progress indicator
        function hideLoadingProgress() {
            $('#houzez-progressive-loader').fadeOut(500, function () {
                $(this).remove();
            });
        }

        // Bind import functionality for newly added templates
        function bindImportForNewTemplates() {
            $(
                '#houzez-library-modal .elementor-template-library-template-insert'
            )
                .off('click')
                .on('click', function () {
                    // Use the same import logic as before
                    const templateId = $(this).data('id');
                    importSingleTemplate(templateId);
                });
        }

        // Extract import functionality for reuse
        function importSingleTemplate(templateId) {
            showLoader();

            var config = {
                data: {
                    source: 'houzez',
                    edit_mode: true,
                    display: true,
                    template_id: 'houzez_' + templateId,
                    with_page_settings: false,
                },
                success: function success(data) {
                    if (data && data.content) {
                        // Log cache information if available
                        if (data.houzez_cache_info) {
                            const cacheInfo = data.houzez_cache_info;
                            const cacheMessage = `🏠 Houzez Template Import | ID: ${
                                cacheInfo.template_id
                            } | Source: ${
                                cacheInfo.cache_source
                            } | API Cached: ${
                                cacheInfo.api_cached ? 'Yes' : 'No'
                            }`;
                            console.log(cacheMessage, cacheInfo);

                            // Show cache status notification
                            let cacheStatusClass = 'houzez-info';
                            let cacheStatusText = '';

                            if (cacheInfo.cache_source === 'local_storage') {
                                cacheStatusText =
                                    '⚡ Template loaded from local storage (fastest)';
                                cacheStatusClass = 'houzez-success';
                            } else if (
                                cacheInfo.cache_source === 'remote_api'
                            ) {
                                if (cacheInfo.api_cached) {
                                    cacheStatusText =
                                        '📱 Template loaded from API cache';
                                    cacheStatusClass = 'houzez-info';
                                } else {
                                    cacheStatusText =
                                        '🌐 Template loaded from live API (slower)';
                                    cacheStatusClass = 'houzez-error';
                                }
                            }

                            // Show temporary cache status notification
                            if (cacheStatusText) {
                                $(
                                    `<div class="houzez-notice ${cacheStatusClass}">${cacheStatusText}</div>`
                                )
                                    .prependTo(
                                        $(
                                            '#houzez-library-modal #elementor-template-library-templates-container'
                                        )
                                    )
                                    .delay(3000)
                                    .fadeOut();
                            }
                        }

                        elementor.getPreviewView().addChildModel(data.content);
                        dialog.hide();
                        setTimeout(function () {
                            hideLoader();
                        }, 2000);
                        activateUpdateButton();
                    } else {
                        $(
                            '<div class="houzez-notice houzez-error">The element can\'t be loaded from the server.</div>'
                        ).prependTo(
                            $(
                                '#houzez-library-modal #elementor-template-library-templates-container'
                            )
                        );
                        hideLoader();
                    }
                },
                error: function () {
                    $(
                        '<div class="houzez-notice houzez-error">The element can\'t be loaded from the server.</div>'
                    ).prependTo(
                        $(
                            '#houzez-library-modal #elementor-template-library-templates-container'
                        )
                    );
                    hideLoader();
                },
            };

            return elementorCommon.ajax.addRequest('get_template_data', config);
        }

        // Display templates in the modal (legacy function for backward compatibility)
        function displayTemplates(response) {
            // Debug: Log the response to check data structure
            console.log('Houzez Templates Response (Legacy):', response);

            // Validate response structure
            if (
                !response ||
                !response.elements ||
                !Array.isArray(response.elements)
            ) {
                console.error('Invalid template response structure', response);
                showError('Invalid template data structure.');
                hideLoader();
                return;
            }

            var itemTemplate = wp.template(
                'elementor-houzez-library-modal-item'
            );
            var itemOrderTemplate = wp.template(
                'elementor-houzez-library-modal-order'
            );

            $(
                '#houzez-library-modal #elementor-template-library-templates-container'
            ).empty();
            $(
                '#houzez-library-modal #elementor-template-library-filter-toolbar-remote'
            ).empty();

            $(itemTemplate(response)).appendTo(
                $(
                    '#houzez-library-modal #elementor-template-library-templates-container'
                )
            );
            $(itemOrderTemplate(response)).appendTo(
                $(
                    '#houzez-library-modal #elementor-template-library-filter-toolbar-remote'
                )
            );

            importTemplate();
            setupSyncButton();
        }

        // Show error message
        function showError(message) {
            $(
                '<div class="houzez-notice houzez-error">' + message + '</div>'
            ).appendTo(
                $(
                    '#houzez-library-modal #elementor-template-library-templates-container'
                )
            );
        }

        // Setup sync button functionality
        function setupSyncButton() {
            $('#houzez-library-modal #houzez-sync-templates')
                .off('click')
                .on('click', function () {
                    var $btn = $(this);
                    var originalHtml = $btn.html();

                    // Stop any ongoing progressive loading
                    progressiveLoading.isLoading = false;
                    hideLoadingProgress();

                    $btn.prop('disabled', true).html(
                        '<i class="eicon-loading eicon-animation-spin"></i> ' +
                            houzez_library_ajax.syncing_text
                    );

                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'houzez_sync_templates',
                            nonce: houzez_library_ajax.nonce,
                        },
                        success: function (response) {
                            if (response.success) {
                                // Show success message with sync details
                                let successMessage =
                                    houzez_library_ajax.sync_success;
                                if (response.data.downloaded) {
                                    successMessage += ` (${response.data.downloaded} templates)`;
                                }

                                $(
                                    '<div class="houzez-notice houzez-success">' +
                                        successMessage +
                                        '</div>'
                                ).prependTo(
                                    $(
                                        '#houzez-library-modal #elementor-template-library-templates-container'
                                    )
                                );

                                // Reload templates after sync
                                setTimeout(function () {
                                    $('.houzez-notice').remove();
                                    // Reset progressive loading state
                                    progressiveLoading.isLoading = false;
                                    progressiveLoading.currentBatch = 0;
                                    progressiveLoading.allTemplates = [];
                                    loadTemplates();
                                }, 2000);
                            } else {
                                $(
                                    '<div class="houzez-notice houzez-error">' +
                                        (response.data.message ||
                                            houzez_library_ajax.sync_error) +
                                        '</div>'
                                ).prependTo(
                                    $(
                                        '#houzez-library-modal #elementor-template-library-templates-container'
                                    )
                                );
                            }
                        },
                        error: function () {
                            $(
                                '<div class="houzez-notice houzez-error">' +
                                    houzez_library_ajax.sync_error +
                                    '</div>'
                            ).prependTo(
                                $(
                                    '#houzez-library-modal #elementor-template-library-templates-container'
                                )
                            );
                        },
                        complete: function () {
                            $btn.prop('disabled', false).html(originalHtml);
                            setTimeout(function () {
                                $('.houzez-notice').fadeOut();
                            }, 5000);
                        },
                    });
                });
        }

        function showLoader() {
            $(
                '#houzez-library-modal #elementor-template-library-templates'
            ).hide();
            $('#houzez-library-modal .elementor-loader-wrapper').show();
        }

        function hideLoader() {
            $(
                '#houzez-library-modal #elementor-template-library-templates'
            ).show();
            $('#houzez-library-modal .elementor-loader-wrapper').hide();
        }

        function activateUpdateButton() {
            $('#elementor-panel-saver-button-publish').toggleClass(
                'elementor-disabled'
            );
            $('#elementor-panel-saver-button-save-options').toggleClass(
                'elementor-disabled'
            );
        }

        function importTemplate() {
            $(
                '#houzez-library-modal .elementor-template-library-template-insert'
            )
                .off('click')
                .on('click', function () {
                    const templateId = $(this).data('id');
                    importSingleTemplate(templateId);
                });

            $(
                '#houzez-library-modal .elementor-templates-modal__header__close'
            ).on('click', () => {
                dialog.hide();
                hideLoader();
            });

            $(
                '#houzez-library-modal #elementor-template-library-filter-text'
            ).on('keyup', function () {
                var searchValue = $(this).val();
                console.log(searchValue);

                //var search = $(this).val().toLowerCase();
                var search = String($(this).val()).toLowerCase(); // Convert to string explicitly

                /*var search = search.replace(/\s/g, '-');
				alert(search);*/
                var activeTab = document
                    .querySelector(
                        '#elementor-houzez-library-header-menu .elementor-active'
                    )
                    .getAttribute('data-tab');

                $('#houzez-library-modal')
                    .find('.elementor-template-library-template')
                    .each(function () {
                        const $this = $(this);
                        const slug = $this.data('slug');
                        const type = $this.data('type');
                        const name = $this.data('name');

                        if (name.includes(search) && type.includes(activeTab)) {
                            $this.show();
                        } else {
                            $this.hide();
                        }
                    });
            });

            // Filter by tag
            $(
                '#houzez-library-modal #elementor-template-library-filter-subtype'
            ).on('change', function () {
                var val = $(this).val();

                $('#houzez-library-modal')
                    .find('.elementor-template-library-template-block')
                    .each(function () {
                        var $this = $(this);
                        var slug = String($this.data('slug')).toLowerCase();

                        if (slug.indexOf(val) > -1 || val == 'all') {
                            $this.show();
                        } else {
                            $this.hide();
                        }
                    });
            });

            function setActiveTab(tab) {
                $(
                    '#houzez-library-modal .elementor-template-library-menu-item'
                ).removeClass('elementor-active');
                const activeTab = $('#houzez-tab-' + tab);
                activeTab.addClass('elementor-active');

                document
                    .querySelectorAll(
                        '#houzez-library-modal .elementor-template-library-template'
                    )
                    .forEach((e) => {
                        const type = e.getAttribute('data-type');
                        e.style.display = type === tab ? 'block' : 'none';

                        if (tab === 'template') {
                            $('#elementor-template-library-filter').hide();
                        } else {
                            $('#elementor-template-library-filter').show();
                        }
                    });
            }

            setActiveTab('block');

            // Filter by type
            $('#houzez-library-modal .elementor-template-library-menu-item').on(
                'click',
                function () {
                    setActiveTab($(this).data('tab'));
                }
            );
        }
    });
})(jQuery);
