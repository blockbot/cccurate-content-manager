<?php

/**
* @package Curate Content Helper
* @version 1.0 
**/

/*
Plugin Name: Curate Content Helper
Plugin URI: cccurate.com
Description: Add interface elements to make adding content easier
Author: Joey Dehnert
Version: 1.0
Author URI: insertculture.com
*/

$curate_content = new curate_content();

class curate_content {
	
	const LANG = "curate_content_textdomain";
	
	public function __construct(){
				
		// calls init function that registers package content type and taxonomy
		add_action("init", array($this, "init"));

		// load scripts and styles
		// add_action("admin_enqueue_scripts", array($this, "curate_content_scripts_styles"));

		// add meta boxes
		add_action("add_meta_boxes", array($this, "add_video_source_options"));

		// custom admin nav order
		add_filter('custom_menu_order', array($this, "custom_menu_order"));
		add_filter('menu_order', array($this, "custom_menu_order"));

		// programmatically update WP posts
		add_action("wp_ajax_curate_update_posts", array($this, "curate_update_posts"));
		add_action("wp_ajax_nopriv_curate_update_posts", array($this, "curate_update_posts"));

		// actions to run on save hook
		add_action("post_updated", array($this, "save_video"));


	}

	public function init(){

		$labels = array(
			'name' => _x('Videos', 'post type general name'),
			'singular_name' => _x('Video', 'post type singular name'),
			'add_new' => _x('Add New', 'Video'),
			'add_new_item' => __('Add New Video'),
			'edit_item' => __('Edit Video'),
			'new_item' => __('New Video'),
			'view_item' => __('View Video'),
			'search_items' => __('Search Videos'),
			'not_found' =>  __('Nothing found'),
			'not_found_in_trash' => __('Nothing found in Trash'),
			'parent_item_colon' => ''
		);
	 
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => "video",
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => true,
			'menu_position' => null,
			'supports' => array('title','editor','custom-fields','page-attributes', 'author', 'excerpt','thumbnail'),
			'taxonomies' => array('category', 'post_tag')
		  );
	 
		register_post_type( 'video' , $args );

		$labels = array(
	        'name' => _x('Stream Videos', 'post type general name'),
	        'singular_name' => _x('Stream Video', 'post type singular name'),
	        'add_new' => _x('Add New', 'Stream Video'),
	        'add_new_item' => __('Add New Stream Video'),
	        'edit_item' => __('Edit Stream Video'),
	        'new_item' => __('New Stream Video'),
	        'view_item' => __('View Stream Video'),
	        'search_items' => __('Search Stream Videos'),
	        'not_found' =>  __('Nothing found'),
	        'not_found_in_trash' => __('Nothing found in Trash'),
	        'parent_item_colon' => ''
	    );
	 
	    $args = array(
	        'labels' => $labels,
	        'public' => true,
	        'publicly_queryable' => true,
	        'show_ui' => true,
	        'query_var' => "stream-video",
	        'rewrite' => true,
	        'capability_type' => 'post',
	        'has_archive' => true,
	        'hierarchical' => true,
	        'menu_position' => null,
	        'supports' => array('title','editor','custom-fields','page-attributes', 'author', 'excerpt','thumbnail'),
	        'taxonomies' => array('category', 'post_tag')
	      );
	 
	    register_post_type( 'stream-video' , $args );
		
	}

	public function custom_menu_order($menu_ord) {

		if (!$menu_ord) return true;
     
	    return array(
	        'index.php', // Dashboard
	        'separator1', // First separator
	        'edit.php?post_type=video', // Videos
	        'edit.php?post_type=stream-video', // Stream Videos
	        'separator2', // Second separator
	        'edit.php?post_type=page', // Pages
	        'upload.php', // Media
	        'link-manager.php', // Links
	        'edit-comments.php', // Comments
	        'separator3', // Third separator
	        'themes.php', // Appearance
	        'plugins.php', // Plugins
	        'users.php', // Users
	        'tools.php', // Tools
	        'options-general.php', // Settings
	        'separator-last', // Last separator
	    );

	}

	public function curate_content_scripts_styles(){

		$plugins_url = plugins_url();
		// wp_enqueue_style('package-css', $plugins_url . '/jd-packages/css/package-css.css');
		wp_enqueue_script('package-js', $plugins_url . '/curate-content/js/curate-content.js', '', false, true);

	}

	public function add_video_source_options(){
		
		$class_methods = get_class_methods('curate_content');

		add_meta_box(
			'video_sources',
			__("Video Sources", self::LANG),
			array($this, 'set_video_sources'),
			'video'
		);

		add_meta_box(
			'video_id',
			__("Video ID", self::LANG),
			array($this, 'set_video_ID'),
			'video'
		);

		add_meta_box(
			'video_sources',
			__("Video Sources", self::LANG),
			array($this, 'set_video_sources'),
			'stream-video'
		);

		add_meta_box(
			'video_id',
			__("Video ID", self::LANG),
			array($this, 'set_video_ID'),
			'stream-video'
		);
		
	}

	public function set_video_sources(){
		include "html/video-source-radio-buttons.php";
	}

	public function set_video_ID(){
		include "html/video-source-id-input.php";
	}

	public function save_video($post_id){

		// avoiding infinite loop
		remove_action("post_updated", array($this,"save_video"));

		$video_source = $_POST["curate-video-sources"];
		$video_id = $_POST["curate-video-id"];
		$video_source_exists = get_post_meta(get_the_ID(), 'Video Source');
		$video_id_exists = get_post_meta(get_the_ID(), $video_source . " ID");
		$video_length = $this->get_video_length($video_source, $video_id);

		if(!empty($video_source_exists)){
			update_post_meta($post_id, "Video Source", $video_source);
		} else{
			add_post_meta($post_id, "Video Source", $video_source, true);
		}

		if(!empty($video_id_exists) && !empty($video_source)){
			update_post_meta($post_id, $video_source . " ID", $video_id);
		} else if (!empty($video_source)){
			add_post_meta($post_id, $video_source . " ID", $video_id, true);
		}

		if(!empty($video_length)){
			add_post_meta($post_id, "Video Length", $video_length, true);
		}

		add_action('post_updated', array($this,"save_video"));

	}

	public function get_video_length($video_source, $video_id){
		
		// something is breaking here
		if(!empty($video_source[0]) && !empty($video_id[0])){

			switch ($video_source) {

				case 'Youtube':
					
					$xml_string = file_get_contents('https://gdata.youtube.com/feeds/api/videos/' . $video_id . '?v=2');
					$xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOWARNING);
					
					if (!$xml) {
						return 0;
						break;
					}

			      	$result = $xml->xpath('//yt:duration[@seconds]');
			      	$total_seconds = (int) $result[0]->attributes()->seconds;

			      	return $total_seconds;

					break;

				case 'Vimeo':

					$vimeo_video = @file_get_contents("http://vimeo.com/api/v2/video/" . $video_id . ".php");

					if($vimeo_video === FALSE) {  
						break;
					}
					
					$vimeo_video_data = unserialize($vimeo_video);
					$total_seconds = $vimeo_video_data[0]["duration"];

					return $total_seconds;

					break;

				case 'Vine':

					return 6;

					break;

				case 'Instagram':

					return 15;

					break;

				default:
					break;
			
			}

		} else {

			return;

		}

		

	}

	public function curate_update_posts(){


		$args = array(
			'post_type' => 'video',
			'posts_per_page' => -1,
		);
		$query = new WP_Query( $args );

		// diebug($query->posts);

		foreach($query->posts as $post) {


			$video_source = get_post_meta($post->ID, 'Video Source');
			$video_id = get_post_meta($post->ID, $video_source[0] . " ID");
			$video_length = get_post_meta($post->ID, 'Video Length');
			$video_length_value = $this->get_video_length($video_source[0], $video_id[0]);

			if(gettype($video_length_value) == "integer"){

				if(!empty($video_length)){
					update_post_meta($post->ID, "Video Length", $video_length_value);
				} else {
					add_post_meta($post->ID, "Video Length", $video_length_value, true);
				}

			} else {

				add_post_meta($post->ID, "Bad Length", true, true);

			}

		}

	}

}

?>