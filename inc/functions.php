<?php

/**
 * Get all ui post types and exclude puffar
 * 
 * @return array of post types
 */
function spathon_puff_post_types(){
	$post_types = get_post_types(array('show_ui' => true), 'names');
	// remove puffar
	$key = array_search('puffar', $post_types);
	if ($key !== false)	{
		unset($post_types[$key]);
	}
	return $post_types;
}








/**
 * Removes empty meta keys
 *
 * @param array $arr the meta array
 * @return no return & make the $arr to update automatic
 */
function puff_meta_clean(&$arr){
	if (is_array($arr))	{
		foreach ($arr as $i => $v){
			if (is_array($arr[$i])) {
				puff_meta_clean($arr[$i]);
				if (!count($arr[$i])) {
					unset($arr[$i]);
				}
			}else{
				if (trim($arr[$i]) == ''){
					unset($arr[$i]);
				}
			}
		}
		if (!count($arr)) {
			$arr = NULL;
		}
	}
}






/**
 * Serialize area name with only allowed characters
 *
 * @param string $area the area name
 * @return a valid string
 */
function ps_puff_validate_area($area){
	return trim(str_replace('_', ' ', preg_replace('/[^\w -\d\xe5\xe4\xf6\xc5\xc4\xd6]/i', '', $area)));
}







