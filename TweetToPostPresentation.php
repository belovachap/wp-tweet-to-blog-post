<?php

require_once 'TweetQuery.php';

class TweetToPostPresentation
{
	public static function render_tweet_query_method_options($selected_method=null)
	{
		foreach(TweetQueryMethod::getMethodsAndNames() as $method=>$name)
		{
			if ($method == $selected_method)
			{
				echo '<option selected="selected" value="' . $method . '">' . $name . '</option>';
			}
			else
			{
				echo '<option value="' . $method . '">' . $name . '</option>';
			}
		}
	}
	
	public static function render_tweet_query_method_select($id=null, $name=null, $selected_method=null)
	{
		$select_element = '<select';
		if (isset($id))
		{
			$select_element .= ' id="' . $id . '"';
		}
		if (isset($name))
		{
			$select_element .= ' name="' . $name . '"';
		}
		$select_element .= '>';
		
		echo $select_element;
		self::render_tweet_query_method_options($selected_method);
		echo '</select>';
	}
}

?>
