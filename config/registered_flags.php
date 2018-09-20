<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

pm_betaflag_register(
	[
		'key' => 'theme-npfp-show_sidebar-v100',
		'title' => 'Show Sidebar',
		'description' => 'Add a sidebar to the post page',
		'author' => 'Charles Jaimet',
		'ab_label' => 'absb',
		'enforced' => false,
	]
);
pm_betaflag_register(
	[
		'key' => 'theme-npfp-election_widget-v208',
		'title' => 'Election Widget',
		'description' => 'Add a CP widget for the 2018 Saskatchewan election.',
		'author' => 'Lavanya Singh',
		'ab_label' => '',
		'enforced' => false,
	]
);
pm_betaflag_register(
	[
		'key' => 'plugin-dfpads-lazyload-v103',
		'title' => 'Lazy Load DFP Ads',
		'description' => 'Add JavaScript to allow bigbox (mid, bot) to lazy load on scroll',
		'author' => 'Steve Browning',
		'ab_label' => 'ab',
		'enforced' => false,
	]
);
pm_betaflag_register(
	[
		'key' => 'plugin-library-wcmpush_terms-v001',
		'title' => 'Push Terms to WCM (Admin)',
		'description' => 'Modify Push functionality to send tags and categories to WCM',
		'author' => 'Sujin Choi',
		'ab_label' => '',
		'enforced' => true,
	]
);
