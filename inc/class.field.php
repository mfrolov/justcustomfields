<?php

class Just_Field{
	
	var $id_base;			// Root id for all fields of this type.
	var $title;				// Name for this field type.
	var $slug = null;
	var $field_options = array(
		'classname' => 'jcf_custom_field',
		'before_widget' => '<div class="form-field">',
		'after_widget' => '</div>',
		'before_title' => '<label>',
		'after_title' => ':</label>',
	);
	
	var $is_new = false;
	var $number = false;	// Unique ID number of the current instance.
	var $id = false;		// Unique ID string of the current instance (id_base-number)
	var $fieldset_id = '';
	var $post_type;
	var $instance = array();     // this is field settings (like title, slug etc)
	
	var $post_ID = 0;
	var $entry = null;        // this is field data for each post
	
	var $field_errors = array();
	
	
	/** 
	 *	Constructor
	 */
	function Just_Field( $id_base, $title, $field_options = array() ){
		$this->id_base = $id_base;
		$this->title = $title;
		$this->field_options = array_merge($this->field_options, $field_options);
		$this->post_type = jcf_get_post_type();
	}
	
	/**
	 *	set class property $this->fieldset_id
	 *	@param   string  $fieldset_id  fieldset string ID
	 */
	function set_fieldset( $fieldset_id ){
		$this->fieldset_id = $fieldset_id;
	}
	
	/**
	 *	set class propreties "id", "number"
	 *	load instance and entries for this field
	 *	@param  string  $id  field id (cosist of id_base + number)
	 */
	function set_id( $id ){
		$this->id = $id;
		// this is add request. so number is 0
		if( $this->id == $this->id_base ){
			$this->number = 0;
			$this->is_new = true;
		}
		// parse number
		else{
			$this->number = str_replace($this->id_base.'-', '', $this->id);

			// load instance data
			$this->instance = jcf_field_settings_get( $this->id );
			if( !empty($this->instance) ){
				$this->slug = $this->instance['slug'];
			}
		}
	}
	
	/**
	 *	set post ID and load entry from wp-postmeta
	 *	@param  int  $post_ID  post ID variable
	 */
	function set_post_ID( $post_ID ){
		$this->post_ID = $post_ID;
		// load entry
		if( !empty($this->slug) ){
			$this->entry = get_post_meta($this->post_ID, $this->slug, true);
		}
	}
	
	/**
	 *	generate unique id attribute based on id_base and number
	 *	@param  string  $str  string to be converted
	 */
	function get_field_id( $str ){
		return 'field-'.$this->id_base.'-'.$this->number.'-'.$str;
	}

	/**
	 *	generate unique name attribute based on id_base and number
	 *	@param  string  $str  string to be converted
	 */
	function get_field_name( $str ){
		return 'field-'.$this->id_base.'['.$this->number.']['.$str.']';
	}
	
	/**
	 * validates instance. normalize different field values
	 * @param array $instance
	 */
	function validate_instance( & $instance ){
		if( $instance['_version'] >= 1.4 ){
			$instance['slug'] = $this->validate_instance_slug($instance['slug']);
		}
	}
	
	/**
	 * validate that slug has first underscore
	 * @param string $slug
	 */
	function validate_instance_slug( $slug ){
		$slug = trim($slug);
		if( !empty($slug) && $slug{0} != '_' ){
			$slug = '_' . $slug;
		}
		return $slug;
	}
	
	/**
	 *	function to show add/edit form to edit field settings
	 *	call $this->form inside
	 */
	function do_form(){
		ob_start();
		
		$op = ($this->id_base == $this->id)? __('Add', JCF_TEXTDOMAIN) : __('Edit', JCF_TEXTDOMAIN);
		?>
		<div class="jcf_edit_field">
			<h3 class="header"><?php echo $op . ' ' . $this->title; ?></h3>
			<div class="jcf_inner_content">
				<form action="#" method="post" id="jcform_edit_field">
					<fieldset>
						<input type="hidden" name="field_id" value="<?php echo $this->id; ?>" />
						<input type="hidden" name="field_number" value="<?php echo $this->number; ?>" />
						<input type="hidden" name="field_id_base" value="<?php echo $this->id_base; ?>" />
						<input type="hidden" name="fieldset_id" value="<?php echo $this->fieldset_id; ?>" />
						<?php
							
							$this->form( $this->instance );
							
							// need to add slug field too
							$slug = esc_attr($this->slug);
						?>
						<p>
							<label for="<?php echo $this->get_field_id('slug'); ?>"><?php _e('Slug:', JCF_TEXTDOMAIN); ?></label>
							<input class="widefat" id="<?php echo $this->get_field_id('slug'); ?>" name="<?php echo $this->get_field_name('slug'); ?>" type="text" value="<?php echo $slug; ?>" />
							<br/><small><?php _e('Machine name, will be used for postmeta field name. (should start from underscore)', JCF_TEXTDOMAIN); ?></small>
						</p>
						<?php
							// enabled field
							if( $this->is_new ){
								$this->instance['enabled'] = 1;
							}
						?>
						<p>
							<label for="<?php echo $this->get_field_id('enabled'); ?>">
								<input class="checkbox" type="checkbox" 
										id="<?php echo $this->get_field_id('enabled'); ?>"
										name="<?php echo $this->get_field_name('enabled'); ?>"
										value="1" <?php checked(true, @$this->instance['enabled']); ?> />
								<?php _e('Enabled', JCF_TEXTDOMAIN); ?></label>
						</p>
						
						<div class="field-control-actions">
							<div class="alignleft">
								<?php if( $op != __('Add', JCF_TEXTDOMAIN) ) : ?>
								<a href="#remove" class="field-control-remove"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a> |
								<?php endif; ?>
								<a href="#close" class="field-control-close"><?php _e('Close', JCF_TEXTDOMAIN); ?></a>
							</div>
							<div class="alignright">
								<?php echo print_loader_img(); ?>
								<input type="submit" value="<?php _e('Save', JCF_TEXTDOMAIN); ?>" class="button-primary" name="savefield">
							</div>
							<br class="clear"/>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<?php		
		$html = ob_get_clean();
		return $html;
	}
	
	/**
	 *	function to save field instance to the database
	 *	call $this->update inside
	 */
	function do_update(){
		$input = $_POST['field-'.$this->id_base][$this->number];
		// remove all slashed from values
		foreach($input as $var => $value){
			if( is_string($value) ){
				$input[$var] = stripslashes($value);
			}
		}
		
		// validate: title should be always there
		if( empty($input['title']) ){
			return array('status' => '0', 'error' => __('Title field is required.', JCF_TEXTDOMAIN));
		}
		
		// get values from real class:
		$instance = $this->update($input, $this->instance);
		$instance['title'] = strip_tags($instance['title']);
		$instance['slug'] = strip_tags($input['slug']);
		$instance['enabled'] = (int)@$input['enabled'];
		
		// starting from vers. 1.4 all new fields should be marked with version of the plugin
		if( $this->is_new ){
			$instance['_version'] = JCF_VERSION;
		}
		// for old records: set 1.34 - last version without versioning the fields
		if( empty($instance['_version']) ){
			$instance['_version'] = 1.34;
		}
		
		// new from version 1.4: validation/normalization
		$this->validate_instance( $instance );
		
		// check for errors
		// IMPORTANT: experimental function
		if( !empty($this->field_errors) ){
			$errors = implode('\n', $this->field_errors);
			return array('status' => '0', 'error' => $errors);
		}
		
		if( $this->is_new ){
			$this->number = jcf_get_fields_index( $this->id_base );
			$this->id = $this->id_base . '-' . $this->number;
		}
		
		// update fieldset
		$fieldset = jcf_fieldsets_get( $this->fieldset_id );
		$fieldset['fields'][$this->id] = $instance['enabled'];
		jcf_fieldsets_update( $this->fieldset_id, $fieldset );
		
		// check slug field
		if( empty($instance['slug']) ){
			$instance['slug'] = '_field_' . $this->id_base . '__' . $this->number;
		}
		
		// save
		jcf_field_settings_update($this->id, $instance);
		
		// return status
		$res = array(
			'status' => '1',
			'id' => $this->id,
			'id_base' => $this->id_base,
			'fieldset_id' => $this->fieldset_id,
			'is_new' => $this->is_new,
			'instance' => $instance,
		);
		return $res;
	}
	
	/**
	 *	function to delete field from the database
	 */
	function do_delete(){
		// remove from fieldset:
		$fieldset = jcf_fieldsets_get( $this->fieldset_id );
		if( isset($fieldset['fields'][$this->id]) )
			unset($fieldset['fields'][$this->id]);
		jcf_fieldsets_update( $this->fieldset_id, $fieldset );
		
		// remove from fields array
		jcf_field_settings_update($this->id, NULL);
	}
	
	/**
	 *	function to save data from edit post page to postmeta
	 *	call $this->save()
	 */
	function do_save(){
		// check that number and post_ID is set
		if( empty($this->post_ID) || empty($this->number) ) return false;
		
		// check that we have data in POST
		if( $this->id_base != 'checkbox' && (
				empty($_POST['field-'.$this->id_base][$this->number]) ||
				!is_array($_POST['field-'.$this->id_base][$this->number])
			)
		   )
		{
			return false;
		}
		
		$input = @$_POST['field-'.$this->id_base][$this->number];
		// get real values
		$values = $this->save( $input );
		// save to post meta
		update_post_meta($this->post_ID, $this->slug, $values);
		return true;
	}
	
	/**
	 *	function that call $this->add_js to enqueue scripts in head section
	 *	do this only on post edit page and if at least one field is exists.
	 *	do this only once
	 */
	function do_add_js(){
		global $jcf_included_assets;
		
		if( !empty($jcf_included_assets['scripts'][get_class($this)]) )
			return false;
		
		if( method_exists($this, 'add_js') ){
			add_action( 'jcf_admin_edit_post_scripts', array($this, 'add_js'), 10 );
		}

		$jcf_included_assets['scripts'][get_class($this)] = 1;
	}
	
	/**
	 *	function that call $this->add_css to enqueue styles in head section
	 *	do this only on post edit page and if at least one field is exists.
	 *	do this only once
	 */
	function do_add_css(){
		global $jcf_included_assets;
		
		if( !empty($jcf_included_assets['styles'][get_class($this)]) )
			return false;
		
		if( method_exists($this, 'add_css') ){
			add_action( 'jcf_admin_edit_post_styles', array($this, 'add_css'), 10 );
		}

		$jcf_included_assets['styles'][get_class($this)] = 1;
	}

	/** Echo the field content.
	 *
	 * Subclasses should over-ride this function to generate their field code.
	 *
	 * @param array $args Display arguments including before_title, after_title, before_field, and after_field.
	 * @param array $instance The settings for the particular instance of the field
	 */
	function field($args, $instance) {
		die('function cf_Field::field() must be over-ridden in a sub-class.');
	}
	
	
	/** Echo the field content.
	 *
	 * Subclasses should over-ride this function to generate their field code.
	 *
	 * @param array $args Display arguments including before_title, after_title, before_field, and after_field.
	 * @param array $instance The settings for the particular instance of the field
	 */
	function save($args, $instance) {
		die('function cf_Field::save() must be over-ridden in a sub-class.');
	}

	/** Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/** Echo the settings update form
	 *
	 * @param array $instance Current settings
	 */
	function form($instance) {
		echo '<p class="no-options-field">' . __('There are no options for this field.', JCF_TEXTDOMAIN) . '</p>';
		return 'noform';
	}
	
	/**
	 * Creating HTML block for visibility options
	 * 
	 * @param type $hidden
	 * @param type $categories
	 * @param type $single_saved_cat
	 * @param type $term
	 * @param type $disabled_flag
	 * 
	 */
	function jfc_visibility_options_print($hidden, $categories, $single_saved_cat = null, $term = null, $disabled_flag = false){
		
		$output_format = 'objects';
		$taxonomies = get_taxonomies($args, $output_format);
		$output = '';
		$output_tax = '';
		$output_terms = '';
		$terms ='';
		if($disabled_flag == true){
			$disabled = 'disabled';
		}
		$output .= '<li class="condition '.$hidden.'">';
		if($disabled_flag == true){
			$output .= '<a href="#" class="delete-condition"></a>';
		}
		$output .= '<span>Taxonomy condition:</span>';
		$output .= '<div class="condition-block">Taxonomy';
			$output_tax .= '<select '.$disabled.' name="taxonomy"  id="taxonomy-select" class="taxonomies '.$hidden.'" '.$hidden.'>';
			foreach($taxonomies as $key=>$single_tax){
				$selected_tax = '';
				//remove Navigation Menus, Link Categories, Format from taxonomy list 
				$exceptions = array('nav_menu', 'link_category', 'post_format');
				if(in_array($single_tax->name, $exceptions)){
					continue;
				}
				if($single_tax->name == $single_saved_cat){
					$selected_tax = 'selected';
				}
				$name = $single_tax->labels->name;
				$output_tax .= '<option '.$selected_tax.' data-taxonomy="'.$key.'" name="taxonomy" id="taxonomy" value="'.$key.'">'.$name.'</option>';

				//Get terms for each taxonomy available
				$terms = get_terms($single_tax->name, $args );
				if(!empty($terms)){
					$output_terms .= '<select name="'.$this->get_field_name('post_categories').'['.$key.'][]" id="'.$this->get_field_id('post_categories').'['.$key.'][]" multiple class="jcf-taxonomy-terms '.$key.' '.$hidden.'" '.$hidden.'>';
					$i=0;
						foreach($terms as $single_term){
							$name = $single_term->name;
							$slug = $single_term->slug;
							$selected = '';
							if(is_array($categories) && !empty($categories[$key]) && empty($hidden)){
								if(in_array($slug, $categories[$key])){
									$selected = 'selected';
								}
							}
							$output_terms .='<option '.$selected.' name="'.$this->get_field_name('post_categories').'['.$key.']['.$i.']" id="'.$this->get_field_id('post_categories').'['.$key.']['.$i.']" value="'.$slug.'">'.$name.'</option>';
							$i++;
						}
					$output_terms .= '</select>';
				}
			}
			$output .= $output_tax;
			$output .= '</select>';
			$output .= '<div class="terms-holder">';
			$output .= $output_terms;
			$output .= '</div></div></li>';
			echo $output;
	}
	
	
}


?>