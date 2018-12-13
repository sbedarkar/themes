<p>
	<?php _e('This entry was posted on', 'h5'); ?> <?php the_time('l, F jS, Y'); ?> <?php _e('at', 'h5'); ?> <?php the_time(); ?> <?php _e('and is filed under', 'h5'); ?> <?php the_category(', ') ?>. 
	<?php _e('You can follow any responses to this entry through the', 'h5'); ?> <?php post_comments_feed_link('RSS 2.0'); ?> <?php _e('feed', 'h5'); ?>. 

	<?php if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) : // both comments and pings open ?>
	
		<?php _e('You can', 'h5'); ?> <a href="#respond"><?php _e('leave a response', 'h5'); ?></a>, <?php _e('or', 'h5'); ?> <a href="<?php trackback_url(); ?>" rel="trackback"><?php _e('trackback', 'h5'); ?></a> <?php _e('from your own site', 'h5'); ?>. 

	<?php elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) : // only pings are open ?>
	
		<?php _e('Responses are currently closed, but you can', 'h5'); ?> <a href="<?php trackback_url(); ?>" rel="trackback"><?php _e('trackback', 'h5'); ?></a> <?php _e('from your own site', 'h5'); ?>. 

	<?php elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) : // comments are open, pings are not ?>
	
		<?php _e('You can skip to the end and leave a response. Pinging is currently not allowed.', 'h5'); ?>

	<?php elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) : // neither comments nor pings are open ?>
	
		<?php _e('Both comments and pings are currently closed.', 'h5'); ?>

	<?php endif; ?>
	
	<?php edit_post_link('Edit this entry', '', '.'); ?>
</p>