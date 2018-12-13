<?php
/*
Plugin Name: Petfinder Listings
Plugin URI: http://www.unboxinteractive.com/
Description: The Petfinder Listings plugin takes advantage of the Petfinder API and can be integrated into your site without coding.
Version: 1.0.12
Author: Bridget Wessel
Author URI: http://www.unboxinteractive.com/
License: GPLv2
*/

/*  Copyright 2014 Bridget Wessel  (email : bridget@unboxinteractive.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/********** Add default styles ************/

ini_set("allow_url_fopen", true);
ini_set("allow_url_include", true);


function petfinder_listings_styles(){
    wp_register_style('petfinder-listings-style', plugins_url( 'petfinder.css', __FILE__ ));
    wp_enqueue_style('petfinder-listings-style');
}
add_action('init', 'petfinder_listings_styles');


/********** Add js to switch out photos ***********/
function petfinder_listings_scripts() {
    if (!is_admin()){
        wp_register_script( 'petfinder_listings_scripts', plugins_url( '/petfinder.js', __FILE__ ));
        wp_enqueue_script( 'petfinder_listings_scripts' );
    }
}

add_action('wp_enqueue_scripts', 'petfinder_listings_scripts', 10, 1);

//add defaults to an array
$petf_options = array(
  'apikey' => 'default',
  'shelter_id' => 'default',
  'thumbnail' => 'pnt',
  'large_image' => 'pn',
  'powered_by' => 'Yes',
  'sort_by' => 'newest'
  
);

include( dirname(__FILE__) . '/featuredpet-widget.php' );

//add settings to database if not already set

add_option('Petfinder-Listings', $petf_options);
$petf_options = get_option('Petfinder-Listings');

if(!isset($petf_options["powered_by"])){
	$petf_options["powered_by"] = "Yes";
}

if(!isset($petf_options["sort_by"])){
	$petf_options["sort_by"] = "newest";
}

// create custom plugin settings menu

add_action('admin_menu', 'petf_admin_page');
add_action( 'widgets_init', create_function('', 'return register_widget("Petfinder_Listings_Featured_Pet");') );

function petf_admin_page() {
	add_options_page('Petfinder Listings Plugin Settings', 'Petfinder Listings', 'manage_options', 'petf', 'petf_options_page');
}

// Add Settings to Plugin Menu
$pluginName = plugin_basename( __FILE__ );

add_filter( 'plugin_action_links_' . $pluginName, 'petf_pluginActions' );

function petf_pluginActions( $links ) {
	$settings_link =
		'<a href="' . get_admin_url( null, 'options-general.php' ) . "?page=petf".'">' .
		__('Settings') . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}



//write settings page
function petf_options_page() {
   global $petf_options;
    if(isset($_POST['save_changes'])) {
        check_admin_referer('petfinder-listings-update_settings');

        $petf_options['apikey']     = trim($_POST['apikey']);
        $petf_options['shelter_id'] = trim($_POST['shelter_id']);
        $petf_options['thumbnail']  = trim($_POST['thumbnail']);
        $petf_options['large_image'] = trim($_POST['large_image']);
		$petf_options['sort_by'] = trim($_POST['sort_by']);
		$petf_options['powered_by'] = trim($_POST['powered_by']);

        update_option('Petfinder-Listings', $petf_options);

        echo "<div class=\"error\">Your changes have been saved successfully!</div>";
    }
    ?>
<div class="wrap">

<h2>Petfinder Settings</h2>

<form name="petfinder-options" action="options-general.php?page=petf" method="post">
    <?php
    if ( function_exists( 'wp_nonce_field' ) )
	    wp_nonce_field( 'petfinder-listings-update_settings' );  ?>

    <table class="form-table">
        <tr valign="top">
        <th scope="row">Your Petfinder API Key (go <a href="http://www.petfinder.com/developers/api-docs" target="_blank">here</a> to get one)</th>
        <td><input type="text" name="apikey" value="<?php echo $petf_options["apikey"] ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Shelter ID</th>
        <td><input type="text" name="shelter_id" value="<?php echo $petf_options["shelter_id"] ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Thumbnail Size (select fixed side size, other side varies depending on ratio of original photo)</th>
        <td><select name="thumbnail">
            <option value="t" <?php echo $petf_options["thumbnail"] == "t" ? "selected='selected'" : ""?>>scaled to 50 pixels tall</option>
            <option value="pnt" <?php echo $petf_options["thumbnail"] == "pnt" ? "selected='selected'" : ""?>>scaled to 60 pixels wide</option>
            <option value="fpm" <?php echo $petf_options["thumbnail"] == "fpm" ? "selected='selected'" : ""?>>scaled to 95 pixels wide</option>
        </select></td>
        </tr>

        <tr valign="top">
        <th scope="row">Large Image Size</th>
        <td><select name="large_image">
            <option value="x" <?php echo $petf_options["large_image"] == "x" ? "selected='selected'" : "" ?>>original, up to 500x500</option>
            <option value="pn" <?php echo $petf_options["large_image"] == "pn" ? "selected='selected'" : "" ?>>up to 320x250</option>
        </select></td>
        </tr>
		
		<tr valign="top">
            <th scope="row">Sort Pets By</th>
            <td><select name="sort_by">
                    <option value="newest" <?php echo $petf_options["sort_by"] == "newest" ? "selected='selected'" : "" ?>>Newest</option>
                    <option value="last_updated" <?php echo $petf_options["sort_by"] == "last_updated" ? "selected='selected'" : "" ?>>Last Updated</option>
                    <option value="name" <?php echo $petf_options["sort_by"] == "name" ? "selected='selected'" : "" ?>>Pet Name</option>
                </select></td>
        </tr>
		
		<tr>
			<th scope="row">Include Powered by Petfinder at bottom of page. Petfinder provides a great, free service for shelters and it is highly recommended you leave this on your Petfinder pages.</th>
			<td><input type="radio" value="Yes" name="powered_by" <?php echo ( $petf_options["powered_by"] == "Yes" )? "checked=\"checked\"" : "" ?>>Yes <input type="radio" value="No" name="powered_by" <?php echo ($petf_options["powered_by"] == "No")? "checked=\"checked\"" : "" ?>>No</td>
		</tr>

        <tr>
            <th colspan="2"><p>After saving, create a page with the shortcode [shelter_list] in the content. View this page to see your listings.</p>
                <p>You can also add the following options to your shortcode<br />[shelter_list shelter_id="WI185" breed="Italian Greyhound" count=75 animal="dog" include_info="no" css_class="igs" contact="Barb Smith"] </p></th>
        </tr>
		
		

    </table>

    <p class="submit">
    <input type="hidden" name="save_changes" value="1" />
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>

<?php }    // end function petf_options_page




function petf_sort_by_last_updated($petA, $petB) {
	if ( (string)$petA->last_update == (string)$petB->last_update ){
		return 0;
	}else if ( (string)$petA->last_update < (string)$petB->last_update ){
		return 1;
	}else{
		return -1;
	}
}

function petf_sort_by_name($petA, $petB) {
	if ( (string)$petA->name == (string)$petB->name ){
		return 0;
	}else if ( (string)$petA->name < (string)$petB->name ){
		return -1;
	}else{
		return 1;
	}
}

function petf_sort_by_newest($petA, $petB) {
	if ( (string)$petA->id == (string)$petB->id ){
		return 0;
	}else if ( (string)$petA->id < (string)$petB->id ){
		return 1;
	}else{
		return -1;
	}
}

add_shortcode('shelter_list','petf_shelter_list');

/** Using shortcode shelter_list grab all animals for this shelter.
 * Available Options: shelter_id (if want to list animals from shelter not set in Petfinder Listings settings), breed, count, animal, include_info, css_class and contact.
 * Breed can be inclusive or exclusive by adding ! before breed name.      ***/
function petf_shelter_list( $atts ) {

    global $petf_options;

    extract( shortcode_atts( array(
		'shelter_id' => $petf_options['shelter_id'],
		'breed' => '',
        'count' => 75,
		'animal' => '',
        'include_info' => 'yes',
        'css_class' => 'pets',
        'contact' => '',
        'include_mixes' => 'no',
        'status' => 'A',
		'sort_by' => $petf_options['sort_by'],
		'petfinder_down_message' => 'Petfinder is down at the moment. Please check back shortly.'
	), $atts, 'shelter_list' ) );
	//get the xml
	$response = wp_remote_get( "http://api.petfinder.com/shelter.getPets?key=" . trim($petf_options["apikey"]) . "&count=" . trim(intval($count)) . "&id=" . trim($shelter_id) . "&status=" . trim($status) . "&output=full", array("timeout" => 120) );
    //echo "http://api.petfinder.com/shelter.getPets?key=" . trim($petf_options["apikey"]) . "&count=" . trim(intval($count)) . "&id=" . trim($shelter_id) . "&status=" . trim($status) . "&output=full";
	$xml = simplexml_load_string(wp_remote_retrieve_body($response));
	
	//print_r($xml);
	if( $xml->header->status->code == "100"){
        $output_buffer = "";
        if( count( $xml->pets->pet ) > 0 ){
            $output_buffer .= "<div class=\"" . $css_class . "\">";
			$pets = $xml->pets->pet;
			
			$avail_pets = petf_set_up_pets_array($pets);			
			
			switch ($sort_by){
				case "last_updated":
					uasort($avail_pets, 'petf_sort_by_last_updated');
					break;
				case "name":
					uasort($avail_pets, 'petf_sort_by_name');
					break;
				case "newest":
					uasort($avail_pets, 'petf_sort_by_newest');
					break;
			}
			
			foreach($avail_pets as $dog){
                
				$continue = false;
                if(($animal == "" || strtolower( $dog->animal ) == strtolower( $animal )) && ( $contact == "" || strtolower( $dog->contact_name ) == strtolower( $contact ) ) ){
					if( $breed != "" ){
					   foreach( $dog->breeds as $this_breed ){
						   if( strpos( $breed, "!" ) === false ){
                               if( strtolower($breed) == strtolower( $this_breed ) && ( $dog->mix == "no" || ( $dog->mix == "yes" && $include_mixes == "yes" ) ) ){
                               	   	$continue = true;
									break; //looking for specific breed and it was found
							   }
						   }else{
							   if( strtolower( str_replace( "!", "", $breed ) ) == strtolower( $this_breed ) && $dog->mix == "no" ){
								   break; //looking for other breeds and this breed was found
							   }else{
								   $continue = true;
							   }
						   }
					   }
					}else{
						$continue = true;
					}
					if( $continue ){
						$output_buffer .= "<div class=\"dog " . $dog->animal . "\"><div class=\"name\"><a name=\"" . $dog->id . "\">". $dog->name . "</a></div>";
						$output_buffer .= "<div class=\"images\">";
						
                        if(count($dog->images) > 0){
												
							$output_buffer .= $dog->get_photos($petf_options["large_image"], $petf_options["thumbnail"]);
						}
						
                        if($include_info == "yes"){
                            $output_buffer .= $dog->get_info();
                        }
						$output_buffer .= "</div>"; //close images
						
						$output_buffer .= "<div class=\"description\">" . $dog->description . "</div><div class=\"features\">" . $dog->age . ", " . (($dog->sex == "M") ? "Male" : "Female") . ", " . $dog->get_size() . "</div></div>";
						$output_buffer .= "<div style=\"clear: both; \"></div>";
					}
				} //animal does not match
            }
			if($petf_options['powered_by'] == "Yes"){
				$output_buffer .= "<div class=\"powered_by\">Powered by <a href=\"http://www.petfinder.com\" target=\"_blank\">Petfinder.com</a></div>";
			}
            $output_buffer .= "</div>";
        }else{
           $output_buffer .= "No pets are listed for this shelter at this time.  Please check back soon.";
        }
	}else{
		$output_buffer = $petfinder_down_message . "<br />Error Code returned: " . $xml->header->status->code;
    }

   return $output_buffer;
}


add_shortcode('get_pet','petf_get_pet');

/** Using shortcode get_pet grab one pet from petfinder.
 * Available Options: pet_id *required*, include_info, css_class.  ***/
function petf_get_pet( $atts ) {

    global $petf_options;
	
	extract( shortcode_atts( array(
		'pet_id' => 1234,
        'include_info' => 'yes',
        'css_class' => 'pets',
		'petfinder_down_message' => 'Petfinder is down at the moment. Please check back shortly.'
	), $atts, 'get_pet' ) );

    $output_buffer = "";
	if(is_numeric($pet_id) && intval($pet_id) > 0 ){
        $response = wp_remote_get( "http://api.petfinder.com/pet.get?key=" . $petf_options["apikey"] . "&id=" . $pet_id );
         /***** Uncomment line below to view XML from Petfinder above results on page. *****/
        //echo  "http://api.petfinder.com/pet.get?key=" . $petf_options["apikey"] . "&id=" . $pet_id;
        $xml = simplexml_load_string(wp_remote_retrieve_body($response));
		//var_dump($xml);
		if( $xml->header->status->code == "100"){
            if( count( $xml->pet ) > 0 ){
                $output_buffer .= "<div class=\"" . $css_class . "\">";
				
				$avail_pets = petf_set_up_pets_array($xml->pet);		
				
                foreach( $avail_pets as $dog ){
                    $output_buffer .= "<div class=\"dog\"><div class=\"name\"><a name=\"" . $dog->id . "\">". $dog->name . "</a></div>";
                    $output_buffer .= "<div class=\"images\">";
                    $firsttime = true;
                    if(count($dog->images) > 0){
                        $output_buffer .= $dog->get_photos($petf_options["large_image"], $petf_options["thumbnail"]);
                    }
                    if( !$firsttime ){
                        //not first time so there are thumbnails to wrap up in a div.  Closing petfinder-thumbnails
                        $output_buffer .= "</div>";
                    }
                    if($include_info == "yes"){

                        $output_buffer .= $dog->get_info();
                    }
                    $output_buffer .= "</div>"; //close images
                    
                    $output_buffer .= "<div class=\"description\">" . $dog->description . "</div><div class=\"features\">" . $dog->age . ", " . (($dog->sex == "M") ? "Male" : "Female") . ", " . $dog->get_size() . "</div></div>";
                    $output_buffer .= "<div style=\"clear: both; \"></div>";
                }
                if($petf_options['powered_by'] == "Yes"){
                    $output_buffer .= "<div class=\"powered_by\">Powered by <a href=\"http://www.petfinder.com\" target=\"_blank\">Petfinder.com</a></div>";
                }
                $output_buffer .= "</div>";
            }else{
               $output_buffer .= "This pet was not found on Petfinder.";
            }
		}else{
            $output_buffer = $petfinder_down_message . "<br />Error Code returned: " . $xml->header->status->code;
        }
    }else{
        $output_buffer .= "Invalid Pet ID supplied.";
    }
    return $output_buffer;
}

function petf_set_up_pets_array($pets){
	$avail_pets = array();
	foreach( $pets as $dog ){
		$this_pet = new petf_AvailPet($dog->id, $dog->name, $dog->animal, $dog->mix, $dog->sex, $dog->description, $dog->size, $dog->age, $dog->lastUpdate, $dog->contact->name);
		
		$options = array();
		foreach( $dog->options->option as $option ){
			$options[] = $option;
		}				
		$this_pet->options = $options;
		
		$breeds = array();
		foreach( $dog->breeds->breed as $this_breed ){
			$breeds[] = $this_breed;
		}
		$this_pet->breeds = $breeds;
		
		$photo_array = array();
		if(count($dog->media->photos) > 0){
			foreach( $dog->media->photos->photo as $photo ){
				$photoid = strval($photo["id"]);
				if( array_key_exists($photoid, $photo_array) ) {
					$photo_array[$photoid][strval($photo["size"])] = $photo;
				}else{
					$photo_array[$photoid] = array(strval($photo["size"]) => $photo);
				}
			}
		}
		$this_pet->images = $photo_array;
		
		$avail_pets[strval($dog->id)] = $this_pet;
	}
	return $avail_pets;
}

class petf_AvailPet{
	function __construct($id, $name, $animal, $mix, $sex, $description, $size, $age, $last_update, $contact_name){
		$this->id = $id;
		$this->name = $name;
		$this->animal = $animal;
		$this->mix = $mix;
		$this->sex = $sex;
		$this->description = $description;
		$this->size = $size;
		$this->age = $age;
		$this->last_update = $last_update;
		
		$this->contact_name = $contact_name;
	}

	public $id;
	public $name;
	public $animal;
	public $mix;
	public $sex;
	public $description;
	public $size;
	public $age;
	public $last_update;
	public $contact_name;
	public $images = array();
	public $options = array();
	public $breeds = array();	
	
	public function get_size(){
		switch ($this->size){
		case "L":
			return "Large";
			break;
		case "M":
			return "Medium";
			break;
		case "S":
			return "Small";
			break;
		default:
			return "Not known";
			break;
		}
	}
	
	public function get_info(){
		$output = "<ul class=\"pet-options\">";

		$firsttime = true;
		foreach( $this->breeds as $this_breed ){
			if($firsttime){
				$output .= "<li class=\"breeds\">";
				$firsttime = false;
			}else{
				$output .= ", ";
			}
			$output .=  $this_breed;
		}
		if(!$firsttime){
			$output .= "</li>";
		}

		$icons = "";
		foreach( $this->options as $option ){
			switch($option){
				case "noCats":
					$icons .= "<img src=\"http://www.petfinder.com/images/search/no-cat.gif\" width=\"36\" height=\"21\" alt=\"Prefers home without cats\" title=\"Prefers home without cats\" />";
					break;
				case "noDogs":
					$icons .= "<img src=\"http://www.petfinder.com/images/search/no-dogs.gif\" width=\"41\" height=\"21\" alt=\"Prefers home without dogs\" title=\"Prefers home without dogs\" />";
					break;
				case "noKids":
					$icons .= "<img src=\"http://www.petfinder.com/images/search/no-kids.gif\" width=\"34\" height=\"21\" alt=\"Prefers home without small kids\" title=\"Prefers home without small kids\" />";
					break;
				case "specialNeeds":
					$icons .= "<img src=\"http://www.petfinder.com/images/search/spec_needs.gif\" width=\"18\" height=\"20\" alt=\"Special Needs\" title=\"Special Needs\" />";
				case "altered":
					$output .= "<li class=\"altered\">Spayed/Neutered</li>";
					break;
				case "hasShots":
					$output .= "<li class=\"hasShots\">Up-to-date with routine shots</li>";
					break;
				case "housebroken":
					$output .= "<li class=\"housebroken\">Housebroken</li>";
					break;
			}
		}
		if($icons != ""){
			$output .= "<li class=\"icon-options\">" . $icons . "</li>";
		}
		$output .= "</ul>";
		
		return $output;
	}
	
	public function get_photos($large_img, $thumbnail){	
		$firsttime = true;
		$output_buffer = "";
		foreach($this->images as $pho){
			if($firsttime){
				$output_buffer .= "<img class=\"petfinder-big-img\" id=\"img_". $this->id . "\"  src=\"" . $pho[$large_img] . "\" alt=\" . $this->name . \">";
				
				$firsttime = false;
				$output_buffer .= "<div class=\"petfinder-thumbnails\">";
			}								

			$output_buffer .= "<img class=\"petfinder-thumbmail\" onclick=\"switchbigimg('img_" . $this->id . "', '" . $pho[$large_img] . "');return false;\" src=\"" . $pho[$thumbnail] . "\" alt=\" . $this->name . \">";

		}
		
		if( !$firsttime ){
			//not first time so there are thumbnails to wrap up in a div.  Closing petfinder-thumbnails
			$output_buffer .= "</div>";
		}
		return $output_buffer;
	}
}