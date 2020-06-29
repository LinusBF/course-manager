<?php
/**
 * Template Name: Course Page Template
 * Template Post Type: cm_course_page
 * The template for displaying a course page
 *
 */


//Check for cookie
CmUserManager::updateSessionFromCookie();

//Check if user is set
if(!isset($_SESSION['course_user']) && !is_admin()){
	wp_redirect(CmCourseStoreHandler::getStoreURL()."?no_session=true");
}

$iPost_id = get_the_ID();
$iCourseId = CmCourse::getCourseByPageID($iPost_id, true);

//Check if user is entitled to course
if(!is_admin() && !CmUserManager::checkAccess($_SESSION['course_user']['id'], $iCourseId)){
	wp_redirect(CmCourseStoreHandler::getStoreURL()."?no_access=true");
}


add_action('wp_head', function(){echo "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">";});
add_action('wp_head', 'course_page_header', 9999);

function course_page_header(){
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cm_general.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/cmCoursePage.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/cmCoursePageMobile.css'>";
}

?>
<html <?php language_attributes(); ?>>
	<head>
		<?php
		wp_head();
		?>
	</head>
	<body <?php body_class(); ?>>
		<div class="wrap">
			<div id="primary" class="content-area">
				<div class="cm_back_to_store">
					<a class="underline" href="<?php echo CmCourseStoreHandler::getStoreURL(); ?>">
						<?php echo TXT_CM_PAGE_BACK_TO_STORE; ?>
					</a>
				</div>
				<main id="main" class="site-main" role="main">
					<?php

					while ( have_posts() ) : the_post(); ?> <!-- Because the_content() works only inside a WP Loop -->
						<div class="container" id="course_page_container">
							<?php the_content(); ?> <!-- Page Content -->
						</div><!-- .entry-content-page -->

						<?php
					endwhile; //resetting the page loop

					?>
					<input id="cm_question_nonce" type="hidden" value="<?php echo wp_create_nonce("cm_answer_question") ?>">
					<input id="cm_answers_nonce" type="hidden" value="<?php echo wp_create_nonce("cm_answers") ?>">
					<input id="cm_ancestry_nonce" type="hidden" value="<?php echo wp_create_nonce("cm_part_ancestry") ?>">
				</main><!-- #main -->
			</div><!-- #primary -->
		</div><!-- .wrap -->
	</body>
</html>
<?php

?>