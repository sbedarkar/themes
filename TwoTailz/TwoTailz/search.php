<?php get_header(); ?>

		<div class="section">

			<?php if (have_posts()) : ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1><?php _e('Search Results for', 'h5'); ?> <?php the_search_query(); ?></h1>
				<ol>
					<?php while (have_posts()) : the_post(); ?>

					<li>
						<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
						<?php the_excerpt(); ?>
					</li>

					<?php endwhile; ?>
				</ol>
			</article>
			
			<nav class="page-nav">
				<p><?php posts_nav_link('&nbsp;&bull;&nbsp;'); ?></p>
			</nav>

			<?php else : ?>

			<article>
				<h1><?php _e('Not Found', 'h5'); ?></h1>
				<p><?php _e('Sorry, but the requested resource was not found on this site.', 'h5'); ?></p>
				<?php get_search_form(); ?>
			</article>

			<?php endif; ?>

		</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>