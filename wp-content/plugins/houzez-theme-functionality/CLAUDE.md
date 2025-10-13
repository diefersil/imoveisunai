# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Houzez Theme Functionality is a WordPress plugin that provides core functionality for the Houzez real estate theme. It includes custom post types, Elementor widgets, shortcodes, and various real estate-specific features.

## Development Commands

This is a WordPress plugin with no build process. Common WordPress development practices apply:

- **Activate Plugin**: Enable through WordPress admin dashboard at Plugins menu
- **Check Plugin Status**: `wp plugin list` (if WP-CLI is available)
- **WordPress Debug Mode**: Set `WP_DEBUG` to `true` in `wp-config.php` for development
- **Clear Cache**: Use WordPress admin or `wp cache flush` if caching plugins are active

## Architecture

### Core Components

1. **Main Plugin File** (`houzez-theme-functionality.php`): Entry point that initializes the plugin and defines constants

2. **Initialization System** (`classes/class-houzez-init.php`): Central orchestrator that:
   - Loads all dependencies and components
   - Manages plugin activation/deactivation
   - Coordinates between different modules

3. **Custom Post Types** (in `classes/`):
   - Properties (`class-property-post-type.php`) - Main real estate listings
   - Agents (`class-agent-post-type.php`)
   - Agencies (`class-agency-post-type.php`)
   - Reviews, Testimonials, Invoices, Memberships, User Packages

4. **Elementor Integration** (`elementor/`):
   - Custom widgets for property display
   - Single property/agent/agency sections
   - Search builders and property carousels
   - Uses traits pattern for shared functionality (`traits/`)

5. **Meta Box Framework** (`extensions/meta-box/`):
   - Bundled Meta Box plugin for custom fields
   - Addons for conditional logic, tabs, groups, and term meta

6. **Shortcodes** (`shortcodes/`): Legacy shortcode support for non-Elementor users

7. **Third-Party Integrations** (`third-party/`): External service integrations

8. **Localization** (`languages/`): Multi-language support with .po/.mo files

### Key Design Patterns

- **Singleton Pattern**: Used in main classes (Houzez, Houzez_Elementor_Extensions)
- **Static Methods**: Post type registrations and utility functions
- **WordPress Hooks**: Extensive use of actions and filters for extensibility
- **Trait Pattern**: Shared functionality in Elementor widgets via traits

### Important Constants

- `HOUZEZ_PLUGIN_URL` - Plugin URL
- `HOUZEZ_PLUGIN_DIR` - Plugin directory path
- `HOUZEZ_VERSION` - Current plugin version
- `HOUZEZ_DB_VERSION` - Database schema version

### Database Considerations

- Custom post types and taxonomies are registered on init
- Plugin stores version info in WordPress options table
- Uses WordPress transients for caching where applicable

### Security Notes

- All files check for `ABSPATH` to prevent direct access
- Uses WordPress nonces for form submissions
- Implements capability checks for admin actions

## Elementor Widget Development

When creating or modifying Elementor widgets:
1. Extend the appropriate base class
2. Use traits from `elementor/traits/` for common functionality
3. Place template parts in `elementor/template-part/`
4. Register widgets in `elementor/elementor.php`

## Version Management

- Version is defined in main plugin file header and `HOUZEZ_VERSION` constant
- Database version tracked separately via `HOUZEZ_DB_VERSION`
- Update both when making breaking changes

## Git Workflow

When committing changes:
- Author commits as "Waqas Riaz" only (no co-authors)
- Follow existing commit message patterns from git log
- Current branch: `feature/houzez-theme-functionality`
- Main branch for PRs: `master`