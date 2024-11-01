<?php
/*
Plugin Name: WPDB Optimize
Description: Wordpress DB optimization tool. Created to reduce of overall Wordpress DB size for improved performance. Tool may be accessed from: Tools > WPDB Optimize. Settings are defined under: Settings > WPDB Optimize.
Author: 15miles
Version: 1.1
License: GPL3
Author URI: https://15miles.com/

Copyright (C) 2018 15miles

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
	
*/

defined( 'ABSPATH' ) or die( 'No direct script access' );

function wpdb_optimize_action_links ( $links, $file ) {
	
	if (!stristr($file, 'wpdb-optimize')) { return $links; }

	$mylinks = array(
		'<a href="' . admin_url( 'options-general.php?page=wpdb-optimize-admin' ) . '">Settings</a>',
		'<a href="' . admin_url( 'tools.php?page=wpdb-optimize-admin&optimize=true') . '">Optimize</a>',
	);
	return array_merge( $mylinks, $links );
}

class WPDBOptimizePage {

	private $options;
	
    public function __construct(){	
		if (is_admin()){
			add_action( 'admin_menu', array($this, 'add_plugin_page'));
			add_action( 'admin_init', array($this, 'page_init'));
			add_filter( 'plugin_action_links', 'wpdb_optimize_action_links', 10, 2);
		}
    }

	function page_init(){ 

		register_setting('wpdb_optimize_group', 'wpdb_optimize_options', array($this, 'sanitize'));
        add_settings_section('section_1', null, null, 'wpdb-optimize-admin');  
        add_settings_field('delete_pingbacks', 'Delete Pingbacks?', array( $this, 'delete_pingbacks_callback' ), 'wpdb-optimize-admin', 'section_1');      
        add_settings_field('delete_trackbacks', 'Delete Trackbacks?', array($this, 'delete_trackbacks_callback' ), 'wpdb-optimize-admin', 'section_1');  
        add_settings_field('delete_unapproved_comments','Delete Unapproved Comments?',array($this, 'delete_comments_callback' ),'wpdb-optimize-admin', 'section_1');
        add_settings_field('delete_spam_comments', 'Delete Spam Comments?', array( $this, 'delete_spam_callback' ), 'wpdb-optimize-admin','section_1'); 
        add_settings_field('delete_unused_terms','Delete Unused Terms?', array( $this, 'delete_unused_terms_callback' ),'wpdb-optimize-admin','section_1'); 		
        add_settings_field('resize_db','Resize Database?', array( $this, 'resize_db_callback' ),'wpdb-optimize-admin','section_1'); 
		
	}
	
    public function add_plugin_page(){
        add_management_page('WPDB Optimize', 'WPDB Optimize', 'manage_options', 'wpdb-optimize-admin', array($this, 'show_tool'));	
		add_options_page('WPDB Optimize Settings Admin', 'WPDB Optimize', 'manage_options', 'wpdb-optimize-admin', array($this, 'show_settings'));
    }


    public function delete_pingbacks_callback(){  ?>
		<div class="checkbox">
			<label><input type="checkbox" id="delete_pingbacks" name="wpdb_optimize_options[delete_pingbacks]" <?php if($this->options['delete_pingbacks']){ echo " checked "; } ?>></label>
			Pingbacks are a type of unnecessary comment that are created when another blog links to you.
		</div>
		<?php
    }

    public function delete_trackbacks_callback(){  ?>
		<div class="checkbox">
			<label><input type="checkbox" id="delete_trackbacks" name="wpdb_optimize_options[delete_trackbacks]" <?php if($this->options['delete_trackbacks']){ echo " checked "; } ?>></label>
			Trackbacks are a type of unnecessary comment that are created in order to notify another blog that you have linked to it.
		</div>
		<?php
    }

    public function delete_comments_callback(){  ?>
		<div class="checkbox">
			<label><input type="checkbox" id="delete_comments" name="wpdb_optimize_options[delete_comments]" <?php if($this->options['delete_comments']){ echo " checked "; } ?>></label>
			Removes comments that have not yet been approved.
		</div>
		<?php
    }

    public function delete_spam_callback(){  ?>
		<div class="checkbox">
			<label><input type="checkbox" id="delete_spam" name="wpdb_optimize_options[delete_spam]" <?php if($this->options['delete_spam']){ echo " checked "; } ?>></label>
			Removes comments in spam status, as well as comment spam identified by Akismet.
		</div>
		<?php
    }
	
	public function delete_unused_terms_callback(){  ?>
		<div class="checkbox">
			<label><input type="checkbox" id="delete_unused_terms" name="wpdb_optimize_options[delete_unused_terms]" <?php if($this->options['delete_unused_terms']){ echo " checked "; } ?>></label>
			Terms include Wordpress Categories and Tags.
		</div>
		<?php
    }
	
	public function resize_db_callback(){?>
		<div class="checkbox">
			<label><input type="checkbox" id="resize_db" name="wpdb_optimize_options[resize_db]" <?php if($this->options['resize_db']){ echo " checked "; } ?>></label>
			Resize database index fields to more appropriate sizing for faster performance.
		</div>
		<?php
    }
	
	public function sanitize($input){
		if(!empty($input)){
			$new_input = array();
			foreach($input as $field => $value){
				if(!is_numeric($value)){
					$new_input[$field] = sanitize_text_field($value);
				} else {
					$new_input[$field] = $value;
				}
			}
			return $new_input;
		}
		return;
    }
	
	private function _get_options(){
		$this->options = get_option( 'wpdb_optimize_options' );
		if(!isset($this->options['delete_pingbacks'])){ $this->options['delete_pingbacks'] = 0; }
		if(!isset($this->options['delete_trackbacks'])){ $this->options['delete_trackbacks'] = 0; }
		if(!isset($this->options['delete_comments'])){ $this->options['delete_comments'] = 0; }
		if(!isset($this->options['delete_spam'])){ $this->options['delete_spam'] = 0; }
		if(!isset($this->options['delete_unused_terms'])){ $this->options['delete_unused_terms'] = 0; }
		if(!isset($this->options['resize_db'])){ $this->options['resize_db'] = 0; }					
	}
	
	public function show_settings(){ 
		$this->_get_options(); ?>	
        <div class="wrap">
            <h1>WPDB Optimize</h1>	
			<form method="post" action="options.php">
			<?php
				settings_fields( 'wpdb_optimize_group' );
				do_settings_sections( 'wpdb-optimize-admin' );
				submit_button('Save Settings');
			?>
			<a href="<?php echo home_url(); ?>/wp-admin/tools.php?page=wpdb-optimize-admin&optimize=true" class="button button-primary">Run Optimization</a>
			</form>
        </div>		
        <?php	
	}
	
    public function show_tool(){ 
	
		$option_values = array(0 => 'Off', 1 => 'On', 'on' => 'On');

		$this->_get_options(); ?>	
        <div class="wrap">
            <h1>WPDB Optimize</h1>
		</div>
		<?php if(!isset($_REQUEST['optimize']) || !$_REQUEST['optimize']){ ?>
			<div class="wrap">
				<p>Backup your DB prior to optimizing.</p>	
				<div id="optimization_settings" class="results" style="margin:10px 0px;">
					<b>Current Settings</b>
					<p>Settings are defined under <a href="<?php echo home_url(); ?>/wp-admin/options-general.php?page=wpdb-optimize-admin">Settings > WPDB Optimize</a>.</p>
					<table>
					<?php foreach($this->options as $option => $value){ ?>
						<tr>
							<td><?php echo ucwords(str_ireplace('_',' ',$option)); ?></td>
							<td><?php echo $option_values[$value]; ?></td>
						</tr>
					<?php } ?>
					</table>
				</div>		
				<form method="post">
					<input type="hidden" name="optimize" value="1" />
					<input type="submit" value="Optimize Now" class="button button-primary" onclick="show_spinner();" />
				</form>			
			</div>	
		<?php } ?>
		<div id="optimization_results" class="results">									
			<div id="spinner" class="spinner" style="float:none; width:auto; height:auto; padding:10px 0 10px 50px; background-position:20px 0;"></div>		
			<?php if(isset($_REQUEST['optimize']) && $_REQUEST['optimize']){ $this->_process(); } ?>			
		</div>
		<script>
			function show_spinner(){
				var element = document.getElementById("spinner");
				element.classList.add("is-active");
			}
		</script>	
        <?php
    }

	private function _process(){		
		$this->_clean_db();	
		$this->_indexes();		
		$this->_columns();	
		$this->_optimize();		
	}

	
	private function _clean_db(){

		global $wpdb;
		
		echo "<h3>Cleaning DB</h3>";
		$queries = array();

		if($this->options['delete_pingbacks']){
			$queries[] = array(
				'sql' => "DELETE FROM {$wpdb->prefix}comments WHERE comment_type = 'pingback'",
				'description' => 'Deleting pingbacks'
			);
		}
		
		if($this->options['delete_trackbacks']){
			$queries[] = array(
				'sql' => "DELETE FROM {$wpdb->prefix}comments WHERE comment_type = 'trackback'",
				'description' => 'Deleting trackbacks'
			);
		}
						
		if($this->options['delete_comments']){
			$queries[] =	array(
				'sql' => "DELETE FROM {$wpdb->prefix}comments WHERE comment_approved = '0'",
				'description' => 'Deleting unapproved comments'
			);
		}
	
		if($this->options['delete_spam']){
			$queries[] =	array(
				'sql' => "DELETE FROM {$wpdb->prefix}comments WHERE comment_approved = 'spam'",
				'description' => 'Deleting comments flagged as spam'
			);
			
			$queries[] = array(
				'sql' => "DELETE FROM {$wpdb->prefix}commentmeta WHERE meta_key LIKE '%akismet%'",
				'description' => 'Deleting comment spam identified by Akismet'
			);
			
		}
		
		$queries[] = array(
			'sql' => "DELETE FROM {$wpdb->prefix}commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM {$wpdb->prefix}comments)",
			'description' => 'Deleting orphaned commentmeta'
		);
	
		$queries[] = array(
			'sql' => "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '%\_transient\_%'",
			'description' => 'Deleting transient data'
		);
	
		$queries[] = array(
			'sql' => "DELETE {$wpdb->prefix}posts FROM {$wpdb->prefix}posts LEFT JOIN {$wpdb->prefix}posts child ON ({$wpdb->prefix}posts.post_parent = child.ID) WHERE ({$wpdb->prefix}posts.post_parent <> 0) AND (child.ID IS NULL)",
			'description' => 'Deleting orphaned child posts'
		);

		$queries[] = array(
			'sql' => "DELETE FROM `{$wpdb->prefix}posts` WHERE `post_status`='trash'",
			'description' => 'Emptying trash'
		);
		

		$queries[] = array(
			'sql' => "DELETE {$wpdb->prefix}postmeta FROM {$wpdb->prefix}postmeta LEFT JOIN {$wpdb->prefix}posts ON ({$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID) WHERE ({$wpdb->prefix}posts.ID IS NULL)",
			'description' => 'Deleting orphaned post meta'
		);
		
		$queries[] = array(
			'sql' => "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = '_edit_lock'",
			'description' => 'Deleting postmeta edit locks'
		);
			
		if($this->options['delete_unused_terms']){
	
			$queries[] = array(
				'sql' => "DELETE FROM {$wpdb->prefix}terms WHERE term_id IN (SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE count = 0 )",
				'description' => 'Deleting unused terms'
			);
			
			$queries[] = array(
				'sql' => "DELETE FROM {$wpdb->prefix}term_taxonomy WHERE term_id not IN (SELECT term_id FROM {$wpdb->prefix}terms)",
				'description' => 'Deleting unused terms taxonomy'
			);
			
			$queries[] = array(
				'sql' => "DELETE FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id not IN (SELECT term_taxonomy_id FROM {$wpdb->prefix}term_taxonomy)",
				'description' => 'Deleting unused terms relationships'
			);

		}
		
		$queries[] = array(
			'sql' => "DELETE {$wpdb->prefix}usermeta FROM {$wpdb->prefix}usermeta LEFT JOIN {$wpdb->prefix}users ON ({$wpdb->prefix}usermeta.user_id = {$wpdb->prefix}users.ID) WHERE ({$wpdb->prefix}users.ID IS NULL)",
			'description' => 'Deleting orphaned usermeta'
		);
			
		foreach($queries as $row){
			$this->_query($row);
		}
		flush();		
	}

	private function _indexes(){
		
		global $wpdb;
		
		echo "<h3>Creating Indexes</h3>";
		$keys = array(
			'options' => 'autoload',
			'term_relationships' => 'term_order'
		);
		foreach($keys as $table => $key){

			$table_name = $wpdb->prefix . $table;
			$indexes = $wpdb->get_results("SHOW INDEX FROM {$table_name}", ARRAY_A);
			
			$found = false;
			foreach($indexes as $index){
				if($index['Key_name'] == $key){
					$found = true;
				}
			}
			
			if($found){ 
				echo "Index: {$key} for {$table_name} already exists<br />";
			} else {
				if($wpdb->query("ALTER TABLE `{$table_name}` ADD INDEX(`{$key}`)")){
					echo "Index: {$key} for {$table_name} created<br />";
				} else {
					echo "Error encounted attempting to create index: {$key} for {$table_name}<br />";
				}
			}	

		}
		flush();
	}

	private function _columns(){

		if(!$this->options['resize_db']){
			return; 
		}
	
		global $wpdb;
		
		$int_types = array(
			'tinyint' => 127,
			'smallint' => 32767,
			'mediumint' => 8388607,
			'int' => 2147483647,
			'bigint' => 9223372036854775807
		);

		$fields = array('comment_id' => 'bigint','post_id' => 'bigint','term_id' => 'bigint','user_id' => 'bigint');
		
		echo "<h3>Resizing Table Columns</h3>";
		
		$last_comment = $wpdb->get_results("SELECT comment_ID FROM {$wpdb->prefix}comments ORDER BY comment_ID desc LIMIT 1", ARRAY_A);
		if(!empty($last_comment)){
			$last_comment_id = $last_comment[0]['comment_ID'];
			$pos = 0;
			foreach($int_types as $type => $size){
				if($size < $last_comment_id){ continue; }			
				$fields['comment_id'] = $type;		
				$pos++;
				if($pos > 2){				
					break;
				}
			}
		} else {
			$fields['comment_id'] = 'mediumint';
		}
			
		$last_post = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts ORDER BY ID desc LIMIT 1", ARRAY_A);
		$last_post_id = $last_post[0]['ID'];
		$pos = 0;
		foreach($int_types as $type => $size){
			if($size < $last_post_id){ continue; }			
			$fields['post_id'] = $type;		
			$pos++;
			if($pos > 2){				
				break;
			}
		}

		$last_term = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms ORDER BY term_id desc LIMIT 1", ARRAY_A);
		if(!empty($last_term)){
			$last_term_id = $last_term[0]['term_id'];
			$pos = 0;
			foreach($int_types as $type => $size){
				if($size < $last_term_id){ continue; }			
				$fields['term_id'] = $type;		
				$pos++;
				if($pos > 2){				
					break;
				}
			}
		} else {
			$fields['term_id'] = 'mediumint';
		}

		$last_user = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}users ORDER BY ID desc LIMIT 1", ARRAY_A);
		$last_user_id = $last_user[0]['ID'];
		$pos = 0;
		foreach($int_types as $type => $size){
			if($size < $last_user_id){ continue; }			
			$fields['user_id'] = $type;		
			$pos++;
			if($pos > 2){				
				break;
			}
		}
		
		$queries = array(
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}commentmeta` CHANGE `comment_id` `comment_id` {$fields['comment_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing commentmeta comment id'
			),
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}comments` CHANGE `comment_ID` `comment_ID` {$fields['comment_id']} UNSIGNED NOT NULL AUTO_INCREMENT",
				'description' => 'Resizing comments comment id'
			),
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}comments` CHANGE `comment_post_ID` `comment_post_ID` {$fields['post_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing comments post id'
			),
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}comments` CHANGE `comment_parent` `comment_parent` {$fields['comment_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing comments parent id'
			),
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}comments` CHANGE `user_id` `user_id` {$fields['user_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing comments user id'
			),			
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}postmeta` CHANGE `post_id` `post_id` {$fields['post_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing postmeta post id'
			),
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}posts` CHANGE `ID` `ID` {$fields['post_id']} UNSIGNED NOT NULL AUTO_INCREMENT",
				'description' => 'Resizing posts id'
			),			
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}posts` CHANGE `post_parent` `post_parent` {$fields['post_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing posts parent id'
			),	
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}posts` CHANGE `comment_count` `comment_count` {$fields['comment_id']} NOT NULL DEFAULT '0'",
				'description' => 'Resizing posts comment count'
			),	
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}posts` CHANGE `post_author` `post_author` {$fields['user_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing posts user id'
			),	
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}termmeta` CHANGE `term_id` `term_id` {$fields['term_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing termmeta term id'
			),	
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}terms` CHANGE `term_id` `term_id` {$fields['term_id']} UNSIGNED NOT NULL AUTO_INCREMENT",
				'description' => 'Resizing terms term id'
			),	
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}terms` CHANGE `term_group` `term_group` {$fields['term_id']} NOT NULL DEFAULT '0'",
				'description' => 'Resizing terms term group'
			),					
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}term_taxonomy` CHANGE `term_id` `term_id` {$fields['term_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing term_taxonomy term id'
			),	
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}users` CHANGE `ID` `ID` {$fields['user_id']} UNSIGNED NOT NULL AUTO_INCREMENT",
				'description' => 'Resizing users id'
			),
			array(
				'sql' => "ALTER TABLE `{$wpdb->prefix}usermeta` CHANGE `user_id` `user_id` {$fields['user_id']} UNSIGNED NOT NULL DEFAULT '0'",
				'description' => 'Resizing usersmeta user id'
			),			
			
		);
		
		foreach($queries as $row){
			$this->_query($row);
		}
		flush();
				
	}
	
	private function _optimize(){
	
		echo "<h3>Optimizing Tables</h3>";

		global $wpdb;
		
		$queries = array(
			array(
				'sql' => "optimize table {$wpdb->prefix}comments",
				'description' => 'Optimizing comments table'
			),
			array(
				'sql' => "optimize table {$wpdb->prefix}commentmeta",
				'description' => 'Optimizing commentmeta table'
			),
			array(
				'sql' => "optimize table {$wpdb->prefix}links",
				'description' => 'Optimizing links table'
			),
			array(
				'sql' => "optimize table {$wpdb->prefix}options",
				'description' => 'Optimizing options table'
			),
			array(
				'sql' => "optimize table {$wpdb->prefix}postmeta",
				'description' => 'Optimizing postmeta table'
			),
			array(
				'sql' => "optimize table {$wpdb->prefix}posts",
				'description' => 'Optimizing posts table'
			),
			array(
				'sql' => "optimize table {$wpdb->prefix}termmeta",
				'description' => 'Optimizing termmeta table'
			),	
			array(
				'sql' => "optimize table {$wpdb->prefix}terms",
				'description' => 'Optimizing terms table'
			),	
			array(
				'sql' => "optimize table {$wpdb->prefix}term_relationships",
				'description' => 'Optimizing term_relationships table'
			),	
			array(
				'sql' => "optimize table {$wpdb->prefix}term_taxonomy",
				'description' => 'Optimizing term_taxonomy table'
			),		
			array(
				'sql' => "optimize table {$wpdb->prefix}usermeta",
				'description' => 'Optimizing usermeta table'
			),		
			array(
				'sql' => "optimize table {$wpdb->prefix}users",
				'description' => 'Optimizing users table'
			),					
		);	
		
		
		foreach($queries as $row){
			$this->_query($row);
		}
		flush();
		
	}
	
	private function _query($row){
		
		global $wpdb;
			
		$wpdb->query($row['sql']);
		echo "{$row['description']}<br />";
					
	}

}

if( is_admin() ){
    $settings_page = new WPDBOptimizePage();
}
