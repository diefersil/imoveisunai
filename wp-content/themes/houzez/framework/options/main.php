<?php
require_once( get_theme_file_path('/framework/options/general.php') );
require_once( get_theme_file_path('/framework/options/translation.php') );
require_once( get_theme_file_path('/framework/options/logo-favicons.php') );
require_once( get_theme_file_path('/framework/options/header.php') );
require_once( get_theme_file_path('/framework/options/topbar.php') );
require_once( get_theme_file_path('/framework/options/splash.php') );
require_once( get_theme_file_path('/framework/options/login-register.php') );
require_once( get_theme_file_path('/framework/options/price-currency.php') );
require_once( get_theme_file_path('/framework/options/typography.php') );
require_once( get_theme_file_path('/framework/options/styling.php') );
require_once( get_theme_file_path('/framework/options/property-detail.php') );
require_once( get_theme_file_path('/framework/options/print-property.php') );
require_once( get_theme_file_path('/framework/options/add-new-property.php') );
require_once( get_theme_file_path('/framework/options/advanced-search.php') );
require_once( get_theme_file_path('/framework/options/map.php') );
require_once( get_theme_file_path('/framework/options/halfmap.php') );
require_once( get_theme_file_path('/framework/options/listing-options.php') );
require_once( get_theme_file_path('/framework/options/taxonomies.php') );
require_once( get_theme_file_path('/framework/options/projects-options.php') );
require_once( get_theme_file_path('/framework/options/contact-forms.php') );
require_once( get_theme_file_path('/framework/options/webhooks.php') );
require_once( get_theme_file_path('/framework/options/reCaptcha.php') );
require_once( get_theme_file_path('/framework/options/membership.php') );
require_once( get_theme_file_path('/framework/options/agents.php') );
require_once( get_theme_file_path('/framework/options/agencies.php') );
require_once( get_theme_file_path('/framework/options/invoices.php') );
require_once( get_theme_file_path('/framework/options/blog.php') );

if( class_exists('Favethemes_Insights') ) {
	require_once( get_theme_file_path('/framework/options/insights.php') );	
}

require_once( get_theme_file_path('/framework/options/emails.php') );
require_once( get_theme_file_path('/framework/options/banner-slider.php') );
require_once( get_theme_file_path('/framework/options/404.php') );
require_once( get_theme_file_path('/framework/options/footer.php') );
require_once( get_theme_file_path('/framework/options/optimization.php') );
require_once( get_theme_file_path('/framework/options/gdpr.php') );
require_once( get_theme_file_path('/framework/options/custom-code.php') );