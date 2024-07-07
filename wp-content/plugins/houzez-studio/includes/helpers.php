<?php
/**
 * Studio Header Footer function
 *
 */

/**
 * Get content single builder.
 */
function fts_load_template_part() {
    $id = get_the_ID();
    $type = get_post_meta($id, 'fts_template_type', true); // 'true' to get single value
    $type = empty($type) ? 'tmp_header' : $type;

    switch ($type) {
        case 'tmp_header':
            $path = FTS_DIR_PATH . 'templates/content/header.php';
            break;
        case 'tmp_footer':
            $path = FTS_DIR_PATH . 'templates/content/footer.php';
            break;
        default:
            $path = FTS_DIR_PATH . 'templates/content/section.php';
    }

    load_template($path);
}


/**
 * Returns the appropriate file suffix based on script debugging settings.
 *
 * @return string The file suffix, '.min' if SCRIPT_DEBUG is false, empty otherwise.
 */
function fts_suffix() {
    return (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
}


/**
 * Fetches the header ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The header ID if set, false otherwise.
 */
function fts_get_header_id() {
    $header_id = HouzezStudio\FTS_Render_Template::instance()->fetch_plugin_settings('tmp_header');
    return $header_id !== '' ? $header_id : false;
}

/**
 * Determines the activation status of the Header.
 *
 * @since  1.0.0
 * @return bool Returns true if the header is active, false if it is inactive.
 */
function fts_header_enabled() {
    return apply_filters('fts_header_enabled', fts_get_header_id() !== false);
}

/**
 * Returns the header template ID.
 *
 * @since  1.0.0
 * @return string|false The header template ID if set, false otherwise.
 */
function fts_header_template_id() {
    return apply_filters('fts_header_template_id', fts_get_header_id());
}

/**
 * Echoes the Header Template.
 *
 * @since  1.0.0
 */
function fts_get_header_template() {
    echo HouzezStudio\FTS_Elementor::get_elementor_template(fts_header_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the header markup.
 *
 * @since  1.0.0
 */
function fts_render_header() {
    if (!fts_header_enabled()) {
        return;
    }
    ?>
    <header itemscope="itemscope" itemtype="http://schema.org/WPHeader">
        <?php fts_get_header_template(); ?>
    </header>
    <?php
}


/**
 * Fetches the footer ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The footer ID if set, false otherwise.
 */
function fts_get_footer_id() {
    $footer_id = HouzezStudio\FTS_Render_Template::instance()->fetch_plugin_settings('tmp_footer');
    return $footer_id !== '' ? $footer_id : false;
}

/**
 * Determines the activation status of the Footer.
 *
 * @since  1.0.0
 * @return bool Returns true if the footer is active, false if it is inactive.
 */
function fts_footer_enabled() {
    return apply_filters('fts_footer_enabled', fts_get_footer_id() !== false);
}

/**
 * Returns the footer template ID.
 *
 * @since  1.0.0
 * @return string|false The footer template ID if set, false otherwise.
 */
function fts_footer_template_id() {
    return apply_filters('fts_footer_template_id', fts_get_footer_id());
}

/**
 * Echoes the Footer Template.
 *
 * @since  1.0.0
 */
function fts_get_footer_template() {
    echo HouzezStudio\FTS_Elementor::get_elementor_template(fts_footer_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the footer markup.
 *
 * @since  1.0.0
 */
function fts_render_footer() {
    if (!fts_footer_enabled()) {
        return;
    }
    ?>
    <footer itemscope="itemscope" itemtype="http://schema.org/WPFooter">
        <?php fts_get_footer_template(); ?>
    </footer>
    <?php
}


/**
 * Fetches the Before Header ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The before header ID if set, false otherwise.
 */
function fts_get_before_header_id() {
    $before_header_id = HouzezStudio\FTS_Render_Template::instance()->fetch_plugin_settings('tmp_before_header');
    return $before_header_id !== '' ? $before_header_id : false;
}

/**
 * Determines the activation status of the before_header.
 *
 * @since  1.0.0
 * @return bool Returns true if the before header is active, false if it is inactive.
 */
function fts_before_header_enabled() {
    return apply_filters('fts_before_header_enabled', fts_get_before_header_id() !== false);
}

/**
 * Returns the Before Header template ID.
 *
 * @since  1.0.0
 * @return string|false The before header template ID if set, false otherwise.
 */
function fts_before_header_template_id() {
    return apply_filters('fts_before_header_template_id', fts_get_before_header_id());
}

/**
 * Echoes the Before Header Template.
 *
 * @since  1.0.0
 */
function fts_get_before_header_template() {
    echo HouzezStudio\FTS_Elementor::get_elementor_template(fts_before_header_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the Before Header markup.
 *
 * @since  1.0.0
 */
function fts_render_before_header() {
    if (!fts_before_header_enabled()) {
        return;
    }
    fts_get_before_header_template();
}

/**
 * Fetches the after header ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The after header ID if set, false otherwise.
 */
function fts_get_after_header_id() {
    $after_header_id = HouzezStudio\FTS_Render_Template::instance()->fetch_plugin_settings('tmp_after_header');
    return $after_header_id !== '' ? $after_header_id : false;
}

/**
 * Determines the activation status of the after_header.
 *
 * @since  1.0.0
 * @return bool Returns true if the after header is active, false if it is inactive.
 */
function fts_after_header_enabled() {
    return apply_filters('fts_after_header_enabled', fts_get_after_header_id() !== false);
}

/**
 * Returns the after header template ID.
 *
 * @since  1.0.0
 * @return string|false The after header template ID if set, false otherwise.
 */
function fts_after_header_template_id() {
    return apply_filters('fts_after_header_template_id', fts_get_after_header_id());
}

/**
 * Echoes the after header Template.
 *
 * @since  1.0.0
 */
function fts_get_after_header_template() {
    echo HouzezStudio\FTS_Elementor::get_elementor_template(fts_after_header_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the after header markup.
 *
 * @since  1.0.0
 */
function fts_render_after_header() {
    if (!fts_after_header_enabled()) {
        return;
    }
    fts_get_after_header_template();
}

/**
 * Fetches the before footer ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The before footer ID if set, false otherwise.
 */
function fts_get_before_footer_id() {
    $before_footer_id = HouzezStudio\FTS_Render_Template::instance()->fetch_plugin_settings('tmp_before_footer');
    return $before_footer_id !== '' ? $before_footer_id : false;
}

/**
 * Determines the activation status of the before_footer.
 *
 * @since  1.0.0
 * @return bool Returns true if the before footer is active, false if it is inactive.
 */
function fts_before_footer_enabled() {
    return apply_filters('fts_before_footer_enabled', fts_get_before_footer_id() !== false);
}

/**
 * Returns the before footer template ID.
 *
 * @since  1.0.0
 * @return string|false The before footer template ID if set, false otherwise.
 */
function fts_before_footer_template_id() {
    return apply_filters('fts_before_footer_template_id', fts_get_before_footer_id());
}

/**
 * Echoes the before footer Template.
 *
 * @since  1.0.0
 */
function fts_get_before_footer_template() {
    echo HouzezStudio\FTS_Elementor::get_elementor_template(fts_before_footer_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the before footer markup.
 *
 * @since  1.0.0
 */
function fts_render_before_footer() {
    if (!fts_before_footer_enabled()) {
        return;
    }
    fts_get_before_footer_template();
}

/**
 * Fetches the after footer ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The after footer ID if set, false otherwise.
 */
function fts_get_after_footer_id() {
    $after_footer_id = HouzezStudio\FTS_Render_Template::instance()->fetch_plugin_settings('tmp_after_footer');
    return $after_footer_id !== '' ? $after_footer_id : false;
}

/**
 * Determines the activation status of the after_footer.
 *
 * @since  1.0.0
 * @return bool Returns true if the after footer is active, false if it is inactive.
 */
function fts_after_footer_enabled() {
    return apply_filters('fts_after_footer_enabled', fts_get_after_footer_id() !== false);
}

/**
 * Returns the after footer template ID.
 *
 * @since  1.0.0
 * @return string|false The after footer template ID if set, false otherwise.
 */
function fts_after_footer_template_id() {
    return apply_filters('fts_after_footer_template_id', fts_get_after_footer_id());
}

/**
 * Echoes the after footer Template.
 *
 * @since  1.0.0
 */
function fts_get_after_footer_template() {
    echo HouzezStudio\FTS_Elementor::get_elementor_template(fts_after_footer_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the after footer markup.
 *
 * @since  1.0.0
 */
function fts_render_after_footer() {
    if (!fts_after_footer_enabled()) {
        return;
    }
    fts_get_after_footer_template();
}
