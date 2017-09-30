<?php
/**
 * The template for displaying the store
 *
 */

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
					<?php
						$oStore = new CmStore();
						$oStoreHandler = new CmCourseStoreHandler();
						$oCourseManager = new CourseManager();

						$aUri = explode("course-store", $_SERVER["REQUEST_URI"]);

						$aCourses = $oStore->getCoursesForStore();

						foreach ($aCourses as $iKey => $oCourse):
							$aCourseOptions = $oStoreHandler->getStoreOptionsForCourse($oCourse->getCourseID());
							$iPrice = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
							$iPrice = floor( $iPrice );
							$sCurrency = $oCourseManager->getOptions()['currency'];
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
												<a class="w3-btn w3-teal cm_flip_link"
												   href="#<?php echo $oCourse->getCourseURLName();?>"><?php
														if($oCourse->getCoursePrice() > 0) {
															echo $iPrice . $sCurrency . "<br>" . TXT_CM_STORE_MORE_INFO;
														} else{
															echo TXT_CM_STORE_FREE;
														}
													?>
												</a>
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
										</div>
									</div>
								</div>
							</div>
						</div>
						<!--<div id="<?php /*echo $oCourse->getCourseURLName();*/?>" class="popup_overlay">
							<div id="buy_course_<?php /*echo $oCourse->getCourseID();*/?>" class="popup">
								<h2><?php /*echo $oCourse->getCourseName();*/?></h2>
								<a class="close" href="#">&times;</a>
								<div class="content">
									<div class="course_text front">
										<p class="course_name"><?php /*echo $oCourse->getCourseName(); */?></p>
										<p class="course_description"><?php /*echo $aCourseOptions['store_description']; */?></p>
									</div>
									<input type="hidden" value="<?php /*echo $oCourse->getCourseID();*/?>">
									<a href="<?php /*echo reset($aUri) . "courses/" . CmPageBuilder::getCourseFirstPageName($oCourse->getCourseID()); */?>">
										<?php
/*										if($oCourse->getCoursePrice() > 0) {
											echo TXT_CM_STORE_BUY . " " . $iPrice . $sCurrency;
										} else{
											echo TXT_CM_STORE_FREE_EMAIL;
										}
										*/?>
									</a>
								</div>
							</div>
						</div>-->
					<?php
						endforeach;
					?>
				</div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();