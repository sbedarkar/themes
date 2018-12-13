<?php

/*	Template Name: No Sidebar	*/

?>

<?php get_header(); ?>

  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    	<div class="body" style="max-width>
			
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<section>
					<?php the_content(); ?>
				</section>
		</div>

	<?php endwhile; else: ?>

		<div class="section">
			<article>
				<p><?php _e('Sorry, no posts matched your criteria.', 'h5'); ?></p>
			</article>
		</div>

	<?php endif; ?>

<?php get_footer(); ?>