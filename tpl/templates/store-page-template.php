<?php
/**
 * The template for displaying the store
 *
 */

//TODO - Handle "no session" GET

if(isset($_GET['new_token']) && $_GET['new_token']){
	if(isset($_COOKIE['cm_token'])){
		CmUserManager::unsetTokenCookie();
	}
	if(isset($_SESSION['course_user'])){
		CmUserManager::resetUserSession();
	}
}

if(isset($_POST['stripeToken']) && isset($_POST['stripeEmail']) && isset($_GET['my_courses'])){
	$aPurchaseRequest = CmPaymentHandler::handlePurchaseRequest();

	if($aPurchaseRequest['status_code'] === 3){
		wp_redirect(CmCourseStoreHandler::getStoreURL());
	}
}

if(isset($_POST['cm_action']) && $_POST['cm_action'] === "get_course" && isset($_GET['my_courses'])){
	$aPurchaseRequest = CmUserManager::getFreeCourse();
}

//Check for cookie
CmUserManager::updateSessionFromCookie();

add_action('wp_head', 'store_header');

function store_header(){
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cm_store.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/flip_animation.css'>
		  <link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">
		  <script type='application/javascript' src='".CM_URLPATH."js/store_page.js'></script>";
}

get_header(); ?>

	<div class="wrap">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<div class="container" id="store_container">
					<h4>
					<?php
						if(isset($aPurchaseRequest)){
							($aPurchaseRequest['status_code'] === 1 ? _e("Thank you for your purchase") : _e("Purchase failed"));
						}
					?>
					</h4>
					<div id="auth_container">
						<?php
						if (isset($_GET['my_courses'])):
						?>
							<a href="<?php echo CmCourseStoreHandler::getStoreURL(); ?>">
								<?php echo TXT_CM_STORE_GO_TO_STORE; ?>
							</a>
							<a href="<?php echo CmUserManager::getUserPageURL(); ?>">
								<?php echo TXT_CM_STORE_GO_TO_ANSWERS; ?>
							</a>
							<a class="pull-right switch_token" href="<?php echo CmCourseStoreHandler::getStoreURL()."?new_token=true"; ?>">
								<?php echo TXT_CM_STORE_SWITCH_TOKEN; ?>
							</a>
						<?php
						 elseif (isset($_SESSION['course_user'])):
						?>
							<a href="?my_courses=true">
								<?php echo TXT_CM_STORE_GO_TO_YOUR_COURSES; ?>
							</a>
						<?php else: ?>
							<input id="cm_token_input" type="text">
							 <button id="token_btn" class="w3-btn w3-teal">
								 <?php echo TXT_CM_STORE_ENTER_TOKEN;?>
							 </button>
					    <?php endif; ?>
					</div>
					<?php
						$oStore = new CmStore();
						$oStoreHandler = new CmCourseStoreHandler();
						$oCourseManager = new CourseManager();

						$aUri = explode("course-store", $_SERVER["REQUEST_URI"]);

						$blMyCourses = isset($_GET['my_courses']) && isset($_SESSION['course_user']);

						#TODO - Handle expired courses
						if ($blMyCourses){
							$aCourses = CmUserManager::getPurchasedCourses($_SESSION['course_user']['id']);
						} else {
							$aCourses = $oStore->getCoursesForStore();
						}

						if(count($aCourses) == 0){
							echo "<div class='cm_center w3-text-italic'><p>".TXT_CM_STORE_NO_COURSES."</p></div>";
						}

						foreach ($aCourses as $iKey => $oCourse):
							$aCourseOptions = $oStoreHandler->getStoreOptionsForCourse($oCourse->getCourseID());
							$iPrice    = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
							$iPrice    = floor( $iPrice );
							$sCurrency = $oCourseManager->getOptions()['currency'];
							//TODO - Handle expired courses. Maybe continue loop if user don't have access???
					?>
						<div id="<?php echo $oCourse->getCourseURLName();?>" class="course_container flip-container w3-card-2"<?php
						if (($iKey + 1) % 4 == 0){
							echo " id='row_last_course'";
						}
						?>>
							<div class="flipper">
								<div class="course_container_front front">
									<div class="course_image_container">
										<img class="course_image" src="<?php
											$image_url = wp_get_attachment_url( $aCourseOptions['store_image'] );
											echo ($image_url !== false ? $image_url: CM_URLPATH.'gfx/no_image.jpg');
										?>">
									</div>
									<div class="small-flip-container" ontouchstart="this.classList.toggle('hover');">
										<p class="course_name"><?php echo $oCourse->getCourseName(); ?></p>
										<div class="small-flipper">
											<div class="course_text small-front">
												<p class="course_description"><?php echo $aCourseOptions['store_description']; ?></p>
											</div>
											<div class="course_text cm_center small-back">
												<?php if(isset($_SESSION['course_user']) && CmUserManager::checkAccess($_SESSION['course_user']['id'],$oCourse->getCourseID())): ?>
													<a class="w3-btn w3-teal buy_course_btn" href="<?php
													echo CmCourseStoreHandler::getLandingPageURL($oCourse->getCourseID());
													?>">
														<?php echo TXT_CM_STORE_GO_TO_COURSE; ?>
													</a>
												<?php else:?>
													<a class="w3-btn w3-teal cm_flip_link"
													   href="#<?php echo $oCourse->getCourseURLName();?>"><?php
														if($oCourse->getCoursePrice() > 0) {
															echo $iPrice . $sCurrency . "<br>" . TXT_CM_STORE_MORE_INFO;
														} else{
															echo TXT_CM_STORE_FREE;
														}
														?>
													</a>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>
								<div class="course_container_back back">
									<a class="close_course" href="#">&times;</a>
									<div class="course_back_content">
										<div class="course_text">
											<p class="course_name"><?php echo $oCourse->getCourseName(); ?></p>
											<p class="course_description"><?php echo $oCourse->getCourseDescription(); ?></p>
											<br>
											<div class="cm_center">
												<a class="w3-btn w3-teal buy_course_btn" href="<?php
															echo CmCourseStoreHandler::getLandingPageURL($oCourse->getCourseID());
														?>">
													<?php
													if($oCourse->getCoursePrice() > 0) {
														echo TXT_CM_STORE_LEARN_MORE . " " . $iPrice . $sCurrency;
													} else{
														echo TXT_CM_STORE_FREE_LEARN_MORE;
													}
													?>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php
						endforeach;
					?>
				</div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();