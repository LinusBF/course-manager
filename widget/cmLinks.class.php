<?php
/*
Plugin Name: Course Manager Links
Plugin URI:
Description: Links to all course parts
Author: Linus Bein Fahlander
Version: 0.2
Author URI:
*/
/*
function cmLinks_init() {
	register_widget(cmLinks);
}

//Widget class

class cmLinks extends WP_Widget{

	function cmLinks() {
		$widget_options = array(
			'classname' => 'cmLinks',
			'description' => 'Show all relavent course links'
		);

		$this->WP_Widget('cmLinks', 'Course Manager Links', $widget_options);
	}

	//show widget form in Widgets

	function form($instance) {
		//Get all course names
		$cl_names = cmLinks_Get_Excerpts();


		$defaults = array('course_name' => 'You dont have any courses yet');
		$instance = wp_parse_args((array) $instance, $defaults);

		$course_name = esc_attr($instance['course_name']);

		$cl_name_options = "<option value=''>Please Select a Name</option>";

		foreach ($cl_names as $cl_name) {
			if(!$cl_name == '' || !$cl_name == null){
				$cl_name_options .= "<option value='".$cl_name."'".selected( $instance['course_name'], $cl_name ).">".$cl_name."</option>";
			}
		}

		echo "<p>Course Name:
			<select name='".$this->get_field_name('course_name')."' class='widefat'>
			".$cl_name_options."
			</select>
			</p>
		";
	}

	//save widget form

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		$instance['course_name'] = strip_tags($new_instance['course_name']);

		return $instance;
	}

	//show widget in page

	function widget($args, $instance) {
		if(cmLinks_Check_Access()){
		global $wpdb;
		global $post;
		extract($args);

		$course_name = $instance['course_name'];
		//Get course of this site

		$showCheck = true;
		$thisPageID = $post->ID;
		$thisPageID_sql = intval($thisPageID);
		$course_name_sql = sanitizeMySQL($course_name);


		$cmLinks_CourseCheck = $wpdb->get_results(
					"
					SELECT post_excerpt
					FROM $wpdb->posts
					WHERE ID = $thisPageID_sql
					AND post_type = 'page'
					"
				);

		$currentPageExcerpt = $cmLinks_CourseCheck[0]->post_excerpt;

		//Find the other course links

		$cmLinks_CoursePages = $wpdb->get_results(
					"
					SELECT ID, post_title
					FROM $wpdb->posts
					WHERE post_excerpt = '$course_name_sql'
					AND post_type = 'page'
					ORDER BY post_title ASC
					"
				);
		$test = $cmLinks_CoursePages[0]->post_title;
		//show only if course page
		if($currentPageExcerpt == $course_name && $currentPageExcerpt != ""){
			echo $before_widget;
			echo $before_title.'Kurs Delar'.$after_title;

			//print content
			echo "<div class='textwidget'>";
			foreach ($cmLinks_CoursePages as $cmLinks_CoursePage) {
				$cmLinks_url = esc_url(get_page_link($cmLinks_CoursePage->ID));
				echo "<a href='".$cmLinks_url."' >".$cmLinks_CoursePage->post_title."<br>";
			}
			echo "</div>";

			echo $after_widget;
		}
	}
	}

}

function sanitizeString($var){
	$var = stripslashes($var);
	$var = htmlentities($var);
	$var = strip_tags($var);
	return $var;
}

function sanitizeMySQL($var){
	global $wpdb;
	$var = $wpdb->_real_escape($var);
	$var = sanitizeString($var);
	return $var;
}

function cmLinks_Check_Access(){
	if (!class_exists("UserAccessManager")) {
			return false;	
		}

	global $post;
	$oUserAccessManager = new UserAccessManager();
	$oHandler = $oUserAccessManager->getAccessHandler();

	$oID = $post->ID;
	$check = $oHandler->checkObjectAccess('page', $oID);

	if ($check) {
		return true;
	}
	else{
		return false;
	}
}

function cmLinks_Get_Excerpts(){
	global $wpdb;
	global $post;

	$cl_Excerpts = array();

	$cl_Get_exces = $wpdb->get_results(
					"
					SELECT post_excerpt
					FROM $wpdb->posts
					WHERE post_status = 'publish'
					AND post_type = 'page'
					"
	);

	foreach ($cl_Get_exces as $cl_Get_exce) {
		$cl_exce = $cl_Get_exce->post_excerpt;

		if (!in_array($cl_exce, $cl_Excerpts)) {
			$cl_Excerpts[] = $cl_exce;
		}
	}

	return $cl_Excerpts;
}
?>
*/