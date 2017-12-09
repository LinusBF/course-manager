<?php
/**
 * The template for displaying a landing page
 *
 */

add_action('wp_head', 'landing_page_header');

function landing_page_header(){
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cm_landing_page.css'>
		  <link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">
		  <script type='application/javascript' src='".CM_URLPATH."js/landing_page.js'></script>";
}

get_header(); ?>

	<div class="wrap">
		<div id="primary" class="content-area">
			<main id="main" class="site-main landing_page_main" role="main">
				<?php

				while ( have_posts() ) : the_post(); ?> <!--Because the_content() works only inside a WP Loop -->
					<div class="container" id="landing_page_container">
						<?php the_content(); ?> <!-- Page Content -->
					</div><!-- .entry-content-page -->

					<?php
				endwhile; //resetting the page loop

				?>

				<?php
				$iPost_id = get_the_ID();
				$oCourse = CmCourse::getCourseByLandingPage($iPost_id);
				$aCourseOptions = CmCourseStoreHandler::getStoreOptionsForCourse($oCourse->getCourseID());

				$sSlug = basename(get_permalink());
				$aUri = explode($sSlug, $_SERVER["REQUEST_URI"]);

				$iPrice = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
				$iPrice = floor( $iPrice );
				$sCurrency = $oCourseManager->getOptions()['currency'];
				?>

				<div class="get_course_wrapper">
					<a class="w3-btn w3-teal buy_course_btn" href="<?php
					echo reset($aUri) . "courses/" . CmPageBuilder::getCourseFirstPageName($oCourse->getCourseID());
					?>">
						<?php
						if($oCourse->getCoursePrice() > 0) {
							echo TXT_CM_STORE_BUY . " " . $iPrice . $sCurrency;
						} else{
							echo TXT_CM_STORE_FREE_EMAIL;
						}
						?>
					</a>
				</div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();