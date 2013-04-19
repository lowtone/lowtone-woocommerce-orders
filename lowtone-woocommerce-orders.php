<?php
/*
 * Plugin Name: Orders
 * Plugin URI: http://wordpress.lowtone.nl/plugins/woocommerce-orders/
 * Description: Improved order overview for WooCommerce.
 * Version: 1.0
 * Author: Lowtone <info@lowtone.nl>
 * Author URI: http://lowtone.nl
 * License: http://wordpress.lowtone.nl/license
 */
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\plugins\lowtone\woocommerce\orders
 */

namespace lowtone\woocommerce\orders {

	// Update query if a specific order status is requested

	add_action("load-edit.php", function() {
		$screen = get_current_screen();

		if ("edit-shop_order" != $screen->id)
			return;

		if (!isset($_REQUEST["shop_order_status"]))
			return;

		add_filter("pre_get_posts", function($query) {
			$query->query_vars["tax_query"][] = array(
					"taxonomy" => "shop_order_status",
					"field" => "slug",
					"terms" => $_REQUEST["shop_order_status"]
				);

			return $query;
		});
	});

	// Add views for order statuses

	add_filter("views_edit-shop_order", function($views) {
		$current = isset($_REQUEST["shop_order_status"]) ? $_REQUEST["shop_order_status"] : NULL;

		foreach (orderStatuses() as $status) {
			if ($status->count < 1)
				continue;

			$class = "";

			if ($status->slug == $current) {
				$class = ' class="current"';

				if (isset($views["all"]))
					$views["all"] = str_replace('class="current"', "", $views["all"]);

			}

			$views[] = sprintf('<a href="edit.php?post_type=shop_order&shop_order_status=%s"%s>%s <span class="count">(%d)</span></a>', $status->slug, $class, __($status->name, "lowtone_woocommerce_orders"), $status->count);
		}

		return $views;
	});

	// Register textdomain
	
	add_action("plugins_loaded", function() {
		load_plugin_textdomain("lowtone_woocommerce_orders", false, basename(__DIR__) . "/assets/languages");
	});

	// Functions

	function orderStatuses() {
		return (array) get_terms("shop_order_status", array("hide_empty" => 0, "orderby" => "id"));
	}

}