<?php get_header(); ?>

<div class="<?php the_category_ID(); ?>">
<div><?php echo category_description(); ?></div>

<?php if (have_posts()) : ?>
<?php $post = $posts[0]; // hack: set $post so that the_date() works ?>
<?php if (is_category()) : ?>
<?php endif; ?>		
<?php while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<header>
<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
</header>
<section>
<?php if( get_field('HTimgL') ): ?>
<img class="HTimgL" src="<?php the_field('HTimgL'); ?>" />
<?php endif; ?>
<?php if( get_field('HTimgR') ): ?>
<img class="HTimgR" src="<?php the_field('HTimgR'); ?>" />
<?php endif; ?>

<?php if( get_field('FMimg') ): ?>
<img class="FMimg" src="<?php the_field('FMimg'); ?>" />
<?php endif; ?>

<?php if( get_field('content_type') ): ?>
<div style="margin-top:5px;"><?php the_field('content_type'); ?></div>
<?php endif; ?>

<?php if( get_field('rescued') ): ?>
<div style="margin-top:5px;"><b>Rescued:</b> <?php the_field('rescued'); ?></div>
<?php endif; ?>
    
<?php if( get_field('crossed_rainbow_bridge') ): ?>
<div style="margin-top:5px;"><b>Crossed The Rainbow Bridge:</b> <?php the_field('crossed_rainbow_bridge'); ?></div>
<?php endif; ?>

</section>
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

<?php get_footer(); ?>