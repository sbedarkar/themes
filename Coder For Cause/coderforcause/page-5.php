
<?php get_header(); ?>



<!-- w3-content defines a container for fixed size centered content, 
and is wrapped around the whole page content, except for the footer in this example -->
<div class="w3-content" style="max-width:1400px">

<!-- Header -->
<header class="w3-container w3-center w3-padding-32"> 
  <h1><b><?php the_title(); ?></b></h1>
</header>

<!-- Grid -->
<div class="w3-row">

 <div class="w3-container" id="about">
  <div class="w3-content" style="max-width:700px"> 
   

    <div class="w3-container">
      <?php 
	

	 if( have_posts() ):
		
		while( have_posts() ): the_post(); 
	?>

		<?php 

		get_template_part('content',get_post_format()) ;
		?>

		<?php 
	endwhile;
		
	endif;
			
	?></p>
    
    </div>
  </div>
  <hr>

</div>

  
  
<!-- END Introduction Menu -->
</div>

<!-- END GRID -->
</div><br>

<!-- END w3-content -->
</div>

<?php 
get_footer(); 
?>