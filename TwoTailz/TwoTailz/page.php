<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div class="section">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<section>
					<?php the_content(); ?>
				</section>
			</article>
		</div>

	<?php endwhile; else: ?>

		<div class="section">
			<article>
				<p><?php _e('Sorry, no posts matched your criteria.', 'h5'); ?></p>
			</article>
		</div>

	<?php endif; ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>