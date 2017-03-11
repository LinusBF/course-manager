<?php
/**
 * The template for displaying the store
 *
 */

add_action('wp_head', 'store_header');

function store_header(){
	echo "<link rel='stylesheet' href='".CM_URLPATH."css/cm_store.css'>
		  <link rel='stylesheet' href='".CM_URLPATH."css/flip_animation.css'>
		  <link rel=\"stylesheet\" href=\"https://www.w3schools.com/lib/w3.css\">";
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

						$aCourses = $oStore->getCoursesForStore();

						foreach ($aCourses as $iKey => $oCourse):
							$aCourseOptions = $oStoreHandler->getStoreOptionsForCourse($oCourse->getCourseID());
					?>
						<div class="course_container w3-card-2"<?php
						if (($iKey + 1) % 5 == 0){
							echo " id='row_last_course'";
						}
						?>>
							<div class="course_image_container">
								<img class="course_image" src="<?php echo wp_get_attachment_url( $aCourseOptions['store_image'] ); ?>">
							</div>
							<div class="flip-container" ontouchstart="this.classList.toggle('hover');">
								<div class="flipper">
									<div class="course_text front">
										<p class="course_name"><?php echo $oCourse->getCourseName(); ?></p>
										<p class="course_description"><?php echo $aCourseOptions['store_description']; ?></p>
									</div>
									<div class="course_text back">
										<p class="course_name"><?php echo $oCourse->getCourseName(); ?></p>
										<a href="#" class="w3-btn w3-teal"><?php
												if($oCourse->getCoursePrice() > 0) {
													$iPrice = $oCourse->getCoursePrice() * ( 1 - ( $aCourseOptions['current_discount'] / 100 ) );
													$iPrice = floor( $iPrice );
													echo TXT_CM_STORE_BUY . " " . $iPrice . $oCourseManager->getOptions()['currency'];
												} else{
													echo TXT_CM_STORE_FREE;
												}
											?>
										</a>
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