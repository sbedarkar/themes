<?php get_header(); ?>

		<div class="section">

			<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header>
					<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
					<p><?php _e('Posted on', 'h5'); ?> <?php the_time('F jS, Y'); ?> <?php _e('by', 'h5'); ?> <?php the_author(); ?></p>
				</header>
<?php 
	/*
		Template Name: Archives
	*/ 
?>
<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div class="section">
			<article id="post-<?php the_ID(); ?>">
				<header>
					<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
					<p><?php _e('Posted on', 'h5'); ?> <?php the_time('F jS, Y'); ?> <?php _e('by', 'h5'); ?> <?php the_author(); ?></p>
				</header>
<section>
<?php if( get_field('HTimgL') ): ?>
<img style="max-width:48%;" src="<?php the_field('HTimgL'); ?>" />
<?php endif; ?>
<?php if( get_field('HTimgR') ): ?>
<img style="max-width:48%;" src="<?php the_field('HTimgR'); ?>" />
<?php endif; ?>
<?php the_content(); ?>
</section>
				<footer>
					<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
				</footer>
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
			</article>

			<?php endwhile; ?>

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