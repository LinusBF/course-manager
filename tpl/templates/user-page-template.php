<?php
/**
 * The template for displaying the user account page
 *
 */


//Check for cookie
CmUserManager::updateSessionFromCookie();

//Check if user is entitled to course
if(!isset($_SESSION['course_user'])){
	wp_redirect(CmCourseStoreHandler::getStoreURL()."?no_session=true");
}

add_action('wp_head', 'store_header');

function store_header(){
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cm_user_page.css'>
		  <link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">
		  <script type='application/javascript' src='".CM_URLPATH."js/user_page.js'></script>";
}

get_header(); ?>

	<div class="wrap">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<div class="container" id="user_account_container">
					<?php
					$aCourses = CmUserManager::getAllPartsAndAnswers($_SESSION['course_user']['id']);

					var_dump($aCourses);

					foreach ($aCourses as $iKey => $aCourseAnswers):
						?>
						<div>
							<p><?php echo print_r($aCourseAnswers, true) ?></p>
						</div>
						<?php
					endforeach;
					?>
				</div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();