<?php
//add_action('admin_init', 'download_csv_file');
?>
<h2 style="padding-bottom: 25px;"><?php echo __('Export Fields'); ?></h2>

<form name="ocb_export_member_fields" method="post" action="edit.php?post_type=ocbmembers&page=ocb-settings&block=export&tab=4">
	<table class="form-table" style="margin-top:0;">	
	
		<tr valign="top">
			<th scope="row"><label>Select Fields</label></th>
			<td>
				<div id="select_meta_fields" class="button button-large">Select All / Deselect All</div>
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_short_description" /> Short Description
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_cell_phone" /> Cell Phone
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_cell_phone_public" /> Cell Phone Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_address" /> Address
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_address_public" /> Address Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_birthplace" /> Birthplace
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_birthplace_public" /> Birthplace Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_animal" /> Favorite Animal
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_animal_public" /> Favorite Animal Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_car" /> Favorite Car
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_car_public" /> Favorite Car Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_musical_hero" /> Musical Hero
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_musical_hero_public" /> Musical Hero Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_composer" /> Favorite Composer
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_composer_public" /> Favorite Composer Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_instrument" /> Favorite Instrument
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_instrument_public" /> Favorite Instrument Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_color" /> Favorite Color
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_color_public" /> Favorite Color Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_you_grew_up" /> What did you want to be when you grew up?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_you_grew_up_public" /> Grew Up Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_current_occupation" /> Current Occupation
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_current_occupation_public" /> Current Occupation Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_you_laugh_the_most" /> What makes you laugh the most?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_you_laugh_the_most_public" /> Laugh The Most Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_motivates_you_to_work" /> What motivates you to work hard?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_motivates_you_to_work_public" /> Work Hard Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_proudest_accomplishment" /> Proudest Accomplishment?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_proudest_accomplishment_public" /> Proudest Accomplishment Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_sport" /> Favorite Sport?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_sport_public" /> Favorite Sport Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_only_eat_one_meal" /> If you could only eat one meal for the rest of your life, what would it be?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_only_eat_one_meal_public" /> Only Eat One Meal Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_author" /> Favorite Author?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_favorite_author_public" /> Favorite Author Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_biggest_animal_fear" /> Biggest Animal Fear?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_biggest_animal_fear_public" /> Biggest Animal Fear Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_sing_at_karaoke_night" /> What would you sing at Karaoke night?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_sing_at_karaoke_night_public" /> Karaoke Night Display Public?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_hobbies" /> Hobbies?
                <br />
            	<input type="checkbox" name="ocb_member_fields[]" value="_ocb_hobbies_public" /> Hobbies Display Public?
            </td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label>Select Taxonomies</label></th>
			<td>
				<div id="select_meta_taxonomies" class="button button-large">Select All / Deselect All</div>
                <br />
            	<input type="checkbox" name="ocb_member_taxonomies[]" value="membersection" /> Sections
                <br />
            	<input type="checkbox" name="ocb_member_taxonomies[]" value="memberensembles" /> Ensembles
                <br />
            	<input type="checkbox" name="ocb_member_taxonomies[]" value="instruments" /> Instruments
            </td>
		</tr>			
	
		<tr valign="top">
			<th scope="row"><label></label></th>
			<td>
				<input type="submit" class="button button-primary button-large" value="Download CSV" id="download_csv_button" name="download_csv">
			</td>
		</tr>									
	</table>			
</form>	