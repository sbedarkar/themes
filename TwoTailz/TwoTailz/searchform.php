<form method="get" action="<?php echo home_url('/'); ?>" class="h5-search-form">
	<input type="text" id="s" name="s" value="<?php the_search_query(); ?>">
	<input type="submit" value="Search">
</form>