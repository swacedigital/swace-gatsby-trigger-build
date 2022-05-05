<?php

use WPGatsby\Admin\Settings;

/**
 * @package Swace
 */

/*
Plugin Name: Swace Gatsby Build Trigger
Plugin URI: https://swace.se

Description: A plugin for triggering build on Gatsby Cloud and clearing cache.

Version: 1.0.0
Author: Joakim Nilsson
License: GPLv2 or later
Text Domain: swace
*/

function should_render_trigger_build()
{
  return is_plugin_active('wp-graphql/wp-graphql.php') && is_plugin_active('wp-gatsby/wp-gatsby.php');
}

add_action('admin_bar_menu', 'admin_bar_item', 500);
function admin_bar_item(WP_Admin_Bar $admin_bar)
{
  if (!should_render_trigger_build()) {
    return;
  }

  $admin_bar->add_menu(
    array(
      'id'    => 'menu-trigger-build',
      'parent' => null,
      'group'  => null,
      'title' => 'Trigger Build',
      'meta' => array(
        'html' => '<span id="menu-trigger-js" data-trigger-url=' . admin_url('admin-post.php?action=trigger_build') . '>Trigger Build</span>'
      ),
    )
  );
}

function prefix_admin_trigger_build()
{
  if (!should_render_trigger_build()) {
    return;
  }
  $webhook = Settings::prefix_get_option('builds_api_webhook', 'wpgatsby_settings', false);
  $siteId = basename($webhook);
  $triggerUrl = 'https://webhook.gatsbyjs.com/hooks/builds/trigger/' . $siteId;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $triggerUrl);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-gatsby-cache: false'));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $output = curl_exec($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($httpcode == 204) {
    echo 'Build triggered, it should be done within a couple of minutes';
  } else {
    echo 'Build failed, please try again in a couple of minutes or contact site administrator';
  }
}

add_action('admin_post_trigger_build', 'prefix_admin_trigger_build');

function load_trigger_build_wp_admin_scripts()
{
  wp_enqueue_style('trigger_build_wp_admin_css', plugin_dir_url(__FILE__) . '/trigger-build.css');
  wp_enqueue_script('trigger_build', plugin_dir_url(__FILE__) . '/trigger-build.js', array('jquery'));
}
add_action('admin_enqueue_scripts', 'load_trigger_build_wp_admin_scripts');
