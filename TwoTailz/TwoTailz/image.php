<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div class="section">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header>
					<h1><a href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment"><?php echo get_the_title($post->post_parent); ?></a> &raquo; <?php the_title(); ?></h1>
					<p><a href="<?php echo wp_get_attachment_url($post->ID); ?>"><?php echo wp_get_attachment_image($post->ID, 'medium'); ?></a></p>
				</header>
				<section>
					<?php if (!empty($post->post_excerpt)) the_excerpt(); // caption ?>
					<?php the_content(); // image content ?>

					<nav>
						<p><?php previous_image_link(); ?> &bull; <?php next_image_link(); ?></p>
					</nav>
				</section>
				<footer>
					<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
					<?php the_tags( '<p>Tags: ', ', ', '</p>'); ?>
					<?php get_template_part('lib/inc/post-details'); ?>
				</footer>
			</article>
		</div>

		<?php comments_template(); ?>

		<nav class="page-nav">
			<p><?php previous_post_link(); ?> &bull; <?php next_post_link(); ?></p>
		</nav>

	<?php endwhile; else: ?>

		<div class="section">
			<article>
				<p><?php _e('Sorry, no posts matched your criteria.', 'h5'); ?></p>
			</article>
		</div>

	<?php endif; ?>

<?php get_footer(); ?>