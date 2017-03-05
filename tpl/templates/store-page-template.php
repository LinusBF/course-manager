<?php
/**
 * The template for displaying the store
 *
 */

add_action('wp_head', 'store_header');

function store_header(){
	echo ""; //"<meta content='hello'>";
}

get_header(); ?>

	<div class="wrap">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">

				<?php
					$oStore = new CmStore();
					$aCourses = $oStore->getCoursesForStore();

					foreach ($aCourses as $oCourse):
				?>
					<div>
						<h4><?php echo $oCourse->getCourseName(); ?></h4>
					</div>
				<?php
					endforeach;
				?>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();