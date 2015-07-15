<?php
	
	/**
	 *	register field in global variable. contain info like id_base, title and class name
	 */
	function jcf_field_register( $class_name ){
		global $jcf_fields;

		// check class exists and try to create class object to get title
		if( !class_exists($class_name) ) return false;

		$field_obj = new $class_name();

		$field = array(
			'id_base' => $field_obj->id_base,
			'class_name' => $class_name,
			'title' => $field_obj->title,
		);

		$jcf_fields[$field_obj->id_base] = $field;
	}

	/**
	 *	return array of registered fields (or concrete field by id_base)
	 */
	function jcf_get_registered_fields( $id_base = '' ){
		global $jcf_fields;

		if( !empty($id_base) ){
			return @$jcf_fields[$id_base];
		}

		return $jcf_fields;
	}

	/**
	 *	set fields in wp-options
	 */
	function jcf_field_settings_update( $key, $values = array(), $option_name = '' ){
		if(empty( $option_name )){
			$option_name = jcf_fields_get_option_name();
		}

		$field_settings = jcf_get_options($option_name);
		if( $values === NULL && isset($field_settings[$key]) ){
			unset($field_settings[$key]);
		}

		if( !empty($values) ){
			$field_settings[$key] = $values;
		}

		jcf_update_options($option_name, $field_settings);
	}

	/**
	 *	get fields from wp-options
	 */
	function jcf_field_settings_get( $id = '', $option_name = '', $select_from_db = false){
		if(empty( $option_name )){
			$option_name = jcf_fields_get_option_name();
		}
		if(empty($select_from_db)){
			$jcf_read_settings = jcf_get_read_settings();
			if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
				$jcf_settings = jcf_get_all_settings_from_file();
				$post_type =  str_replace('jcf_fields-', '', $option_name);
				$field_settings = $jcf_settings['field_settings'][$post_type];
			}else{
				$field_settings = jcf_get_options($option_name);
			}
		} else {
			$field_settings = jcf_get_options($option_name);
		}

		if(!empty($id)){
			return @$field_settings[$id];
		}

		return $field_settings;
	}

	/**
	 *	init field object
	 */
	function jcf_init_field_object( $field_mixed, $fieldset_id = '', $option_name = '' ){
		// $field_mixed can be real field id or only id_base
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_mixed);
		$field = jcf_get_registered_fields( $id_base );

		$field_obj = new $field['class_name']();

		$field_obj->set_fieldset( $fieldset_id );
		$field_obj->set_id( $field_mixed, $option_name );

		return $field_obj;
	}

	/**
	 * get next index for save new instance
	 */
	function jcf_get_fields_index( $id_base ){

		$option_name = 'jcf_fields_index';
		$indexes = jcf_get_options($option_name);

		// get index, increase on 1
		$index = (int)@$indexes[$id_base];
		$index ++;

		// update indexes
		$indexes[$id_base] = $index;
		jcf_update_options($option_name, $indexes);

		return $index;
	}
	
	// option name in wp-options table
	function jcf_fields_get_option_name(){
		$post_type = jcf_get_post_type();
		return 'jcf_fields-'.$post_type;
	}

	/**
	 *	parse "Settings" param for checkboxes/selects/multiple selects
	 */
	function jcf_parse_field_settings( $string ){
		$values = array();
		$v = explode("\n", $string);
		foreach($v as $val){
			$val = trim($val);
			if(strpos($val, '|') !== FALSE ){
				$a = explode('|', $val);
				$values[$a[0]] = $a[1];
			}
			elseif(!empty($val)){
				$values[$val] = $val;
			}
		}
		$values = array_flip($values);
		return $values;
	}

?>