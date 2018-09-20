<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

pm_betaflag_register(
	[
		'key' => 'theme-show-sidebar',
		'title' => 'Show Sidebar',
		'description' => 'Add a sidebar to the post page',
		'author' => 'Charles Jaimet',
		'ab_label' => 'absb',
		'enforced' => false,
	]
);
pm_betaflag_register(
	[
		'key' => 'widget-election-2018',
		'title' => 'Election Widget',
		'description' => 'Add a CP widget for the 2018 Saskatchewan election.',
		'author' => 'Lavanya Singh',
		'ab_label' => '',
		'enforced' => false,
	]
);
pm_betaflag_register(
	[
		'key' => 'plugin-dfpads-lazyload',
		'title' => 'Lazy Load DFP Ads',
		'description' => 'Add JavaScript to allow bigbox (mid, bot) to lazy load on scroll',
		'author' => 'Steve Browning',
		'ab_label' => 'ab',
		'enforced' => false,
	]
);
pm_betaflag_register(
	[
		'key' => 'plugin-library-wcmpush-terms',
		'title' => 'Push Terms to WCM (Admin)',
		'description' => 'Modify Push functionality to send tags and categories to WCM',
		'author' => 'Sujin Choi',
		'ab_label' => '',
		'enforced' => true,
	]
);
