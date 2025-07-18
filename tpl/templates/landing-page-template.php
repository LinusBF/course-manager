<?php
/**
 * The template for displaying a landing page
 *
 */

//Check for cookie
CmUserManager::updateSessionFromCookie();

add_action('wp_head', function(){echo "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">";});
add_action('wp_head', 'landing_page_header', 9999);

function landing_page_header(){
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cm_general.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/cm_landing_page.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/cm_landing_page_mobile.css'>
		  <script src='https://js.stripe.com/v3/'></script>";
}

$oCM = new CourseManager();

$iPost_id = get_the_ID();
$oCourse = CmCourse::getCourseByLandingPage($iPost_id);

if(is_bool($oCourse)){
	wp_redirect(CmCourseStoreHandler::getStoreURL()."?course_404=true");
}

if(isset($_SESSION['course_user']) && CmUserManager::checkAccess($_SESSION['course_user']['id'],$oCourse->getCourseID())){
	$sSlug = basename(get_permalink());
	$aUri = explode($sSlug, $_SERVER["REQUEST_URI"]);
  wp_redirect(reset($aUri) . "courses/" . CmPageBuilder::getCourseFirstPageName($oCourse->getCourseID()));
}

get_header(); ?>

	<div class="wrap">
		<div id="primary" class="content-area">
			<main id="main" class="site-main landing_page_main" role="main">
				<div id="purchase_error" <?php echo (!isset($_GET['card_declined']) ? "style='display: none;'": "") ?>>
					<h3><?php echo TXT_CM_STORE_CHECKOUT_CARD_DECLINED; ?></h3>
				</div>
				<?php

				while ( have_posts() ) : the_post(); ?> <!--Because the_content() works only inside a WP Loop -->
					<div class="container" id="landing_page_container">
						<?php the_content(); ?> <!-- Page Content -->
					</div><!-- .entry-content-page -->

					<?php
				endwhile; //resetting the page loop

				$aCourseOptions = CmCourseStoreHandler::getStoreOptionsForCourse($oCourse->getCourseID());

				$iPrice = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
				$iPrice = floor( $iPrice );
				$sCurrency = $oCourseManager->getOptions()['currency'];
				?>
				<div class="get_course_wrapper sf-promo-bar">
					<a class="sf-button standard turquoise" href="#signupModal" data-toggle="modal">
						<?php
						if($oCourse->getCoursePrice() > 0) {
							echo TXT_CM_STORE_BUY . " " . $iPrice . $sCurrency;
						} else{
							echo TXT_CM_STORE_FREE_EMAIL;
						}
						?>
					</a>
				</div>
				<div class="modal fade" id="signupModal" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title"><?php echo $oCourse->getCourseName(); ?></h4>
							</div>
							<div class="modal-body">
                <div id="stripe-error"></div>
								<form action="<?php echo CmCourseStoreHandler::getStoreURL()."?my_courses=true"; ?>" method="post">
									<?php if($oCourse->getCoursePrice() <= 0): ?>
										<div class="form-group">
											<label for="email"><span class="glyphicon glyphicon-email"></span> Email</label>
											<input type="text" class="form-control" id="email"
											       name="email" placeholder="<?php echo TXT_CM_LANDING_PAGE_EMAIL; ?>"
												   <?php echo (isset($_SESSION['course_user']) ? 'value="'.$_SESSION['course_user']['email'].'"' : "") ?>>
										</div>
									<?php endif; ?>
										<div class="checkbox pull-right">
											<input id="subscribe" type="checkbox" name="subscribe">
											<label for="subscribe"><?php echo TXT_CM_LANDING_PAGE_SEND_PROMOTIONS; ?></label>
										</div>
										<input type="hidden" name="course_id" value="<?php echo $oCourse->getCourseID() ?>">
									<?php if($oCourse->getCoursePrice() <= 0): ?>
										<input type="hidden" name="cm_action" value="get_course">
										<button type="submit" class="sf-button standard turquoise">Get Course</button>
									<?php else:?>
                    <input id="course-id-for-stripe" type="hidden" name="course_id" value="<?php echo $oCourse->getCourseID() ?>">
                    <input id="stripe-public-key" type="hidden" name="stripe_public_key" value="<?php echo $oCM->getOptions()['stripe']['publishable_key']; ?>">
                    <button id="stripe-button" type="submit" class="sf-button standard turquoise"><?php echo TXT_CM_STORE_CHECKOUT_BUTTON_TEXT; ?></button>
									<?php endif; ?>
								</form>
							</div>
							<div class="modal-footer">
								<button type="submit" class="sf-button standard lightgrey pull-left" data-dismiss="modal">
									<span class="glyphicon glyphicon-remove"></span> <?php echo TXT_CM_CANCEL; ?></button>
								<div class="pull-right">
									<a class="underline" href="<?php echo CmCourseStoreHandler::getStoreURL()."?modal_open=true"; ?>">
										<p><?php echo TXT_CM_LANDING_PAGE_HAVE_TOKEN; ?></p>
									</a>
									<a class="underline" href="/<?php echo TXT_CM_CONTACT; ?>">
										<p><?php echo TXT_CM_LANDING_PAGE_LOST_TOKEN; ?></p>
									</a>
								</div>
							</div>
						</div>

					</div>
				</div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();