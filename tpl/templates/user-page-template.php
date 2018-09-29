<?php
/**
 * The template for displaying the user account page
 *
 */


//Check for cookie
CmUserManager::updateSessionFromCookie();

//Check if user is set
if ( ! isset( $_SESSION['course_user'] ) ) {
	wp_redirect( CmCourseStoreHandler::getStoreURL() . "?no_session=true" );
}

add_action('wp_head', function(){echo "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">";});
add_action( 'wp_head', 'store_header', 9999);

function store_header() {
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cm_general.css'>
		  <link rel='stylesheet' href='" . CM_URLPATH . "css/cm_user_page.css'>
		  <link rel='stylesheet' href='" . CM_URLPATH . "css/cm_user_page_mobile.css'>
		  <script type='application/javascript' src='" . CM_URLPATH . "js/user_page.js'></script>";
}

get_header(); ?>

	<div class="wrap">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<div class="container" id="user_account_container">
					<?php
					$aCourses = CmUserManager::getAllPartsAndAnswers( $_SESSION['course_user']['id'] );
					$aUri     = explode( "course-account", $_SERVER["REQUEST_URI"] );
					?>
					<?php

					foreach ( $aCourses as $aCourseAnswers ):
						?>
						<h2 class="course-title"><?php echo $aCourseAnswers['course']->getCourseName(); ?></h2>
						<div class="cm_course">
							<?php foreach ( $aCourseAnswers['answers'] as $aCpAnswer ): ?>
								<div class="cm_course_part w3-card-2">
									<?php foreach ( $aCpAnswer['answers'] as $aPAnswer ): ?>
										<?php if ( $aPAnswer['answers'] !== false ): ?>
											<div class="cm_part">
												<div class="cm_answers">
													<header class="w3-container">
														<h3><?php echo $aPAnswer['part']->getTitle() ?></h3>
													</header>
													<?php foreach ( $aPAnswer['answers']['A'] as $iAKey => $sAnswer ): ?>
														<div class="cm_answer_wrapper">
															<h4 class="w3-text-gray"><?php echo $aPAnswer['answers']['Q'][ $iAKey ]; ?></h4>
															<p><?php echo htmlspecialchars($sAnswer, ENT_NOQUOTES); ?></p>
														</div>
													<?php endforeach; ?>
													<footer class="w3-container w3-right-align">
														<a class="sf-button sf-button-rounded standard green default"
														   href="<?php echo reset( $aUri ) . "courses/" . CmPageBuilder::getCoursePageName( $aCpAnswer['course-part']->getCoursePartID() ); ?>">
															<?php echo TXT_CM_USER_PAGE_BACK_TO_COURSE; ?>
														</a>
													</footer>
												</div>
											</div>
										<?php else: ?>
											<div class="cm_part">
												<div class="cm_answers">
													<header class="w3-container">
														<h3><?php echo $aPAnswer['part']->getTitle() ?></h3>
													</header>
													<div class="cm_answer_wrapper">
														<p><?php echo TXT_CM_USER_Q_NOT_ANSWERED; ?></p>
													</div>
													<footer class="w3-container w3-right-align">
														<a class="sf-button sf-button-rounded standard green default"
														   href="<?php echo reset( $aUri ) . "courses/" . CmPageBuilder::getCoursePageName( $aCpAnswer['course-part']->getCoursePartID() ); ?>">
															<?php echo TXT_CM_USER_PAGE_BACK_TO_COURSE; ?>
														</a>
													</footer>
												</div>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php
					endforeach;
					?>
				</div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();