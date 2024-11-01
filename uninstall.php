<?php 

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('wpdb_optimize_options'); 

global $wpdb;

$queries = array();	
$queries[] = "ALTER TABLE `{$wpdb->prefix}commentmeta` CHANGE `comment_id` `comment_id` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}comments` CHANGE `comment_ID` `comment_ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT";
$queries[] = "ALTER TABLE `{$wpdb->prefix}comments` CHANGE `comment_post_ID` `comment_post_ID` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}comments` CHANGE `comment_parent` `comment_parent` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}comments` CHANGE `user_id` `user_id` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}postmeta` CHANGE `post_id` `post_id` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}posts` CHANGE `ID` `ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT";
$queries[] = "ALTER TABLE `{$wpdb->prefix}posts` CHANGE `post_parent` `post_parent` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}posts` CHANGE `comment_count` `comment_count` bigint NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}posts` CHANGE `post_author` `post_author` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}termmeta` CHANGE `term_id` `term_id` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}terms` CHANGE `term_id` `term_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT";
$queries[] = "ALTER TABLE `{$wpdb->prefix}terms` CHANGE `term_group` `term_group` bigint NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}term_taxonomy` CHANGE `term_id` `term_id` bigint UNSIGNED NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE `{$wpdb->prefix}users` CHANGE `ID` `ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT";
$queries[] = "ALTER TABLE `{$wpdb->prefix}usermeta` CHANGE `user_id` `user_id` bigint UNSIGNED NOT NULL DEFAULT '0'";
				
foreach($queries as $sql){	
	$wpdb->query($sql);
}
	
?>