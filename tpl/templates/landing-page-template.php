<?php
/**
 * The template for displaying a landing page
 *
 */

//Check for cookie
CmUserManager::updateSessionFromCookie();

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

				?>

				<?php
				$oCM = new CourseManager();

				$iPost_id = get_the_ID();
				$oCourse = CmCourse::getCourseByLandingPage($iPost_id);

				$sSlug = basename(get_permalink());
				$aUri = explode($sSlug, $_SERVER["REQUEST_URI"]);

				//If the user has access to the course, show link to the first CoursePart
				if(isset($_SESSION['course_user']) && CmUserManager::checkAccess($_SESSION['course_user']['id'],$oCourse->getCourseID())):
				?>
					<div class="get_course_wrapper">
						<a class="w3-btn cm_btn bold" href="<?php echo reset($aUri) . "courses/" . CmPageBuilder::getCourseFirstPageName($oCourse->getCourseID()); ?>">
							<?php echo $oCourse->getCourseParts()[0]->getCoursePartName() ?>
						</a>
					</div>
				<?php
				//If the user does not have access to the course, show link to open checkout modal.
				else:
				$aCourseOptions = CmCourseStoreHandler::getStoreOptionsForCourse($oCourse->getCourseID());

				$iPrice = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
				$iPrice = floor( $iPrice );
				$sCurrency = $oCourseManager->getOptions()['currency'];
				?>
				<div class="get_course_wrapper">
					<a class="w3-btn cm_btn buy_course_btn" href="#signupModal" data-toggle="modal">
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
								<form action="<?php echo CmCourseStoreHandler::getStoreURL()."?my_courses=true"; ?>" method="post">
									<?php if($oCourse->getCoursePrice() <= 0): ?>
										<div class="form-group">
											<label for="email"><span class="glyphicon glyphicon-email"></span> Email</label>
											<input type="text" class="form-control" id="email"
											       name="email" placeholder="<?php echo TXT_CM_LANDING_PAGE_EMAIL; ?>"
												   <?php echo (isset($_SESSION['course_user']) ? 'value="'.$_SESSION['course_user']['email'].'"' : "") ?>>
										</div>
									<?php endif; ?>
										<div class="checkbox">
											<label class="pull-right"><input type="checkbox" name="subscribe"><?php echo TXT_CM_LANDING_PAGE_SEND_PROMOTIONS; ?></label>
										</div>
										<input type="hidden" name="course_id" value="<?php echo $oCourse->getCourseID() ?>">
									<?php if($oCourse->getCoursePrice() <= 0): ?>
										<input type="hidden" name="cm_action" value="get_course">
										<button type="submit" class="w3-btn cm_btn btn-block">Get Course</button>
									<?php else: ?>
										<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
										        data-key="<?php echo $oCM->getOptions()['stripe']['publishable_key']; ?>"
										        data-description='<?php
										            echo TXT_CM_STORE_CHECKOUT_DESCRIPTION." \"".$oCourse->getCourseName()."\"";
										        ?>'
										        data-amount="<?php echo ($iPrice * 100) ?>"
										        data-locale="auto"
										        data-currency="<?php echo $oCM->getOptions()['currency'] ?>"
										        data-zip-code="true"
										        data-label="<?php echo TXT_CM_STORE_CHECKOUT_BUTTON_TEXT; ?>"
												data-email="<?php echo (isset($_SESSION['course_user']) ? $_SESSION['course_user']['email'] : "") ?>"></script>
									<?php endif; ?>
								</form>
							</div>
							<div class="modal-footer">
								<button type="submit" class="w3-button w3-white w3-border w3-round w3-border-red pull-left w3-padding-small w3-hover-red" data-dismiss="modal">
									<span class="glyphicon glyphicon-remove"></span> Cancel</button>
								<p>Already have a <a href="#">Course Token?</a></p>
								<p>Lost your <a href="#">Course Token?</a></p>
							</div>
						</div>

					</div>
				</div>
				<?php endif; ?>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();