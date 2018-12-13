<?php 
	
/*
	Template Name: CV
*/
	
get_header(); ?>


<!-- Page Container -->
<div class="w3-content w3-margin-top" style="max-width:1400px;">

  <!-- The Grid -->
  <div class="w3-row-padding">
  
    <!-- Left Column -->
    <div class="w3-third">
    
      <div class="w3-white w3-text-grey w3-card-4">
        <div class="w3-display-container">
          <?php
			
		//PRINT ONLY SWATI CATEGORY
		$lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Photo');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();
				
		?>
          <div class="w3-display-bottomleft w3-container w3-text-white">
            <h2>Swati Gore</h2>
          </div>
        </div>
        <div class="w3-container">
          <p><i class="fa fa-briefcase fa-fw w3-margin-right w3-large w3-text-teal"></i>Full Stack Web Developer</p>
          <p><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-teal"></i>Marietta, GA</p>
          <p><i class="fa fa-envelope fa-fw w3-margin-right w3-large w3-text-teal"></i>swati.gore2feb@gmail.com</p>
          <p><i class="fa fa-phone fa-fw w3-margin-right w3-large w3-text-teal"></i>312-810-9605</p>
          <hr>

          <p class="w3-large"><b><i class="fa fa-asterisk fa-fw w3-margin-right w3-text-teal"></i>Skills</b></p>
          <p>Wordpress</p>
          <div class="w3-light-grey w3-round-xlarge w3-small">
            <div class="w3-container w3-center w3-round-xlarge w3-teal" style="width:90%">90%</div>
          </div>
          <p>PHP</p>
          <div class="w3-light-grey w3-round-xlarge w3-small">
            <div class="w3-container w3-center w3-round-xlarge w3-teal" style="width:85%">
              <div class="w3-center w3-text-white">85%</div>
            </div>
          </div>
          <p>HTML, CSS, Bootstrap</p>
          <div class="w3-light-grey w3-round-xlarge w3-small">
            <div class="w3-container w3-center w3-round-xlarge w3-teal" style="width:80%">80%</div>
          </div>
          <p>JavaScript</p>
          <div class="w3-light-grey w3-round-xlarge w3-small">
            <div class="w3-container w3-center w3-round-xlarge w3-teal" style="width:75%">75%</div>
          </div>
          <br>

          <p class="w3-large w3-text-theme"><b><i class="fa fa-globe fa-fw w3-margin-right w3-text-teal"></i>Services</b></p>
          <p>Web Page Design</p>
          <div class="w3-light-grey w3-round-xlarge">
            <div class="w3-round-xlarge w3-teal" style="height:24px;width:100%"></div>
          </div>
          <p>Domain, Hosting set-up</p>
          <div class="w3-light-grey w3-round-xlarge">
            <div class="w3-round-xlarge w3-teal" style="height:24px;width:75%"></div>
          </div>
          <p>Content Building</p>
          <div class="w3-light-grey w3-round-xlarge">
            <div class="w3-round-xlarge w3-teal" style="height:24px;width:55%"></div>
          </div>
          <br>
        </div>
        <div class="w3-xlarge w3-hide-small "><br></div>
      </div><br>

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
        
      <div class="w3-container w3-card w3-white">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-certificate fa-fw w3-margin-right w3-xxlarge w3-text-teal"></i>Education</h2>
        <div class="w3-container">
          <h5 class="w3-opacity"><b>Certifications</b></h5>
          <h6 class="w3-text-teal"><i class="fa fa-calendar fa-fw w3-margin-right"></i>Forever</h6>
          <p>New Venture Finance: Startup Funding for Entrepreneurs</p>
          <p>Innovation for Entrepreneurs: From Idea to Marketplace</p>
          <p>Developing Innovative Ideas for New Companies:The First Step in Entrepreneurship</p>
          <hr>
        </div>
        <div class="w3-container">
          <h5 class="w3-opacity"><b>University of Illinois at Chicago</b></h5>
          <h6 class="w3-text-teal"><i class="fa fa-calendar fa-fw w3-margin-right"></i>2011 - 2012</h6>
          <p>Master Degree</p>
          <hr>
        </div>
        <!--<div class="w3-container">-->
        <!--  <h5 class="w3-opacity"><b>University of Technology of Madhya Pradesh</b></h5>-->
        <!--  <h6 class="w3-text-teal"><i class="fa fa-calendar fa-fw w3-margin-right"></i>2005 - 2009</h6>-->
        <!--  <p>Bachelor Degree</p><br>-->
        <!--</div>-->
      </div>

    
      <div class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-certificate fa-fw w3-margin-right w3-xxlarge w3-text-teal"></i>About Me</h2>
        <div class="w3-container">
          <p><?php 
	

	 if( have_posts() ):
		
		while( have_posts() ): the_post(); 
	?>

		<?php 

		get_template_part('content',get_post_format()) ;
		?>

		<?php 
	endwhile;
		
	endif;
			
	?><br>
	<div class="w3-xlarge w3-hide-small "><br><br><br><br></div>
        </div>
        
        

      
    <!-- End Right Column -->
    </div>
    
  <!-- End Grid -->
  </div>
  
  <!-- End Page Container -->
</div>

<?php get_footer(); ?>
