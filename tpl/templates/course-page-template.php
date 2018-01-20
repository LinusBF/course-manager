<?php
/**
 * Template Name: Course Page Template
 * Template Post Type: cm_course_page
 * The template for displaying a course page
 *
 */


//Check for cookie
CmUserManager::updateSessionFromCookie();

//Check if user is entitled to course
if(!isset($_SESSION['course_user'])){
	wp_redirect(CmCourseStoreHandler::getStoreURL()."?no_session=true");
}

$iPost_id = get_the_ID();
$iCourseId = CmCourse::getCourseByPageID($iPost_id, true);

if(!CmUserManager::checkAccess($_SESSION['course_user']['id'], $iCourseId)){
	wp_redirect(CmCourseStoreHandler::getStoreURL()."?no_access=true");
}

add_action('wp_head', 'course_page_header');

function course_page_header(){
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cmCoursePage.css'>
		  <link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">
		  <script type='application/javascript' src='".CM_URLPATH."js/course_page.js'></script>";
}

?>
<html <?php language_attributes(); ?>>
	<head>
		<?php
		wp_head();
		?>
	</head>
	<body <?php body_class(); ?>>
		<div class="page-heading  page-heading-breadcrumbs clearfix">
			<div class="container">
				<div class="heading-text">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</div>
			</div>
		</div>
		<div class="wrap">
			<div id="primary" class="content-area">
				<main id="main" class="site-main" role="main">
					<?php

					while ( have_posts() ) : the_post(); ?> <!--Because the_content() works only inside a WP Loop -->
						<div class="container" id="course_page_container">
							<?php the_content(); ?> <!-- Page Content -->
						</div><!-- .entry-content-page -->

						<?php
					endwhile; //resetting the page loop

					?>
				</main><!-- #main -->
			</div><!-- #primary -->
		</div><!-- .wrap -->
		<?php get_footer(); ?>
	</body>
</html>
<?php

?>