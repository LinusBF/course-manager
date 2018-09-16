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

add_action('wp_head', function(){echo "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">";});
add_action('wp_head', 'store_header', 9999);

function store_header(){
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cm_general.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/cm_store.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/cm_store_mobile.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/flip_animation.css'>
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
							<a class="underline pull-right switch_token" href="<?php echo CmCourseStoreHandler::getStoreURL()."?new_token=true"; ?>">
								<?php echo TXT_CM_STORE_SWITCH_TOKEN; ?>
							</a>
							<a class="underline" href="<?php echo CmUserManager::getUserPageURL(); ?>">
								<?php echo TXT_CM_STORE_GO_TO_ANSWERS; ?>
							</a>
							<a class="underline" href="<?php echo CmCourseStoreHandler::getStoreURL(); ?>">
								<?php echo TXT_CM_STORE_GO_TO_STORE; ?>
							</a>
						<?php
						 elseif (isset($_SESSION['course_user'])):
						?>
							<a class="underline" href="?my_courses=true">
								<?php echo TXT_CM_STORE_GO_TO_YOUR_COURSES; ?>
							</a>
						<?php else: ?>
							 <a class="sf-button standard cm_main_btn cm_small pull-right"  href="#useToken" data-toggle="modal">
								 <?php echo TXT_CM_STORE_ENTER_TOKEN;?>
							 </a>
							<div class="modal fade" id="useToken" role="dialog">
								<div class="modal-dialog modal-sm">

									<!-- Modal content-->
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal">&times;</button>
											<h4 class="modal-title"><?php echo TXT_CM_STORE_TOKEN_TITLE ?></h4>
										</div>
										<div class="modal-body">
											<input id="cm_token_input" type="text">
											<button id="token_btn" type="submit" class="sf-button standard cm_main_btn cm_small pull-right">
												<?php echo TXT_CM_STORE_ENTER_TOKEN ?>
											</button>
										</div>
									</div>

								</div>
							</div>
					    <?php endif; ?>
					</div>
					<div class="courses">
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
							if($blMyCourses){
								$blHasAccess = CmUserManager::checkAccess($_SESSION['course_user']['id'], $oCourse->getCourseID());
							}
							//TODO - Handle expired courses. Maybe continue loop if user don't have access???
					?>
						<div id="<?php echo $oCourse->getCourseURLName();?>"
							 class="course_container flip-container w3-card-2 <?php echo (($blMyCourses && !$blHasAccess) ? 'cm_expired' : '') ?>"
							 data-before-content="<?php echo TXT_CM_STORE_EXPIRED_COURSE ?>">
							<div class="flipper">
								<div class="course_container_front front">
									<div class="course_image_container">
										<img class="course_image" src="<?php
											$image_url = wp_get_attachment_url( $aCourseOptions['store_image'] );
											echo ($image_url !== false ? $image_url: CM_URLPATH.'gfx/no_image.jpg');
										?>">
									</div>
									<div class="small-flip-container" ontouchstart="this.classList.toggle('hover');">
										<p class="course_name"><?php
											$sTitleToPrint = $oCourse->getCourseName();
											$blMultiLineTitle = false;
											if(strlen($sTitleToPrint) > 26){
												$blMultiLineTitle = true;
											}
											echo $sTitleToPrint; ?></p>
										<div class="small-flipper">
											<div class="course_text small-front">
												<p class="course_description"><?php
													$sDescToPrint = stripslashes(htmlspecialchars($aCourseOptions['store_description'], ENT_QUOTES, 'UTF-8'));
													if(strlen($sDescToPrint) > 140){
														$sDescWidth = ($blMultiLineTitle ? 61 : 137);
														$sDescToPrint = mb_strimwidth($sDescToPrint, 0, $sDescWidth, "...");
													}
													echo $sDescToPrint;
												?></p>
											</div>
											<div class="course_text cm_center small-back">
												<?php if(isset($_SESSION['course_user']) && CmUserManager::checkAccess($_SESSION['course_user']['id'],$oCourse->getCourseID())): ?>
													<a class="sf-button sf-button-rounded standard buy_course_btn" href="<?php
													echo CmCourseStoreHandler::getLandingPageURL($oCourse->getCourseID());
													?>">
														<?php echo TXT_CM_STORE_GO_TO_COURSE; ?>
													</a>
												<?php else:?>
													<a class="sf-button sf-button-rounded standard cm_flip_link buy_course_btn"
													   href="#<?php echo $oCourse->getCourseURLName();?>"><?php
														if($oCourse->getCoursePrice() > 0) {
															echo TXT_CM_STORE_MORE_INFO;
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
											<p class="course_description"><?php
												$sCourseDescToPrint = $oCourse->getCourseDescription();
												if(strlen($sCourseDescToPrint) > 265){
													$sCourseDescToPrint = mb_strimwidth($sCourseDescToPrint, 0, 262, "...");
												}
												echo $sCourseDescToPrint;
												?></p>
											<div class="cm_center">
												<a class="sf-button sf-button-rounded standard buy_course_btn" href="<?php
															echo CmCourseStoreHandler::getLandingPageURL($oCourse->getCourseID());
														?>">
													<?php
													if($oCourse->getCoursePrice() > 0) {
														echo TXT_CM_STORE_LEARN_MORE . " (" . $iPrice . $sCurrency . ")";
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
				</div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();