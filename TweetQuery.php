<?php

abstract class TweetQueryMethod
{
	const SEARCH = 0;
	const USER_TIMELINE = 1;
	
	public static function getMethodsAndNames() {
		return array(
			self::SEARCH => "Twitter Search",
			self::USER_TIMELINE => "User Timeline",
		);
	}
}


abstract class AbstractTweetQuery
{
	protected $start_from_tweet_id;

	public function __construct($start_from_tweet_id)
	{
		$this->setStartFromTweetID($start_from_tweet_id);
	}

	public function setStartFromTweetID($tweet_id)
	{
		$this->start_from_tweet_id = $tweet_id;
	}

	public function getStartFromTweetID()
	{
		return $this->start_from_tweet_id;
	}

	// Only update start_from_tweet_id if it hasn't been set or is older (a.k.a. smaller)
	// than the passed ID.
	public function setNewestStartFromTweetID($tweet_id)
	{
		if (isset($this->start_from_tweet_id))
		{
			$this->setStartFromTweetID(max($this->getStartFromTweetID(), $tweet_id));
		}
		else
		{
			$this->setStartFromTweetID($tweet_id);
		}
	}

	abstract public function getMethod();
	abstract public function getTweets($twitter_connection, $max_tweets_returned);
	abstract public function getTitle();
}


class SearchTweetQuery extends AbstractTweetQuery 
{
	protected $search_query;

	public function __construct($search_query, $start_from_tweet_id=null)
	{
		parent::__construct($start_from_tweet_id);
		$this->setSearchQuery($search_query);
	}

	public function setSearchQuery($search_query)
	{
		$this->search_query = $search_query;
	}

	public function getSearchQuery()
	{
		return $this->search_query;
	}

	public function getMethod() 
	{
		return TweetQueryMethod::SEARCH;
	}

	public function getTweets($twitter_connection, $max_tweets_returned)
	{
		$parameters = array(
			'q' => $this->getSearchQuery(),
			'include_entities' => true,
			'count' => $max_tweets_returned,
		);

		if ($this->getStartFromTweetID() !== null) {
			$parameters['since_id'] = $this->getStartFromTweetID();
		}

		$api_result = $twitter_connection->get('search/tweets', $parameters);

		return $api_result->statuses;
	}

	public function getTitle()
	{
		return 'Twitter Search Query for "' . $this->getSearchQuery() . '"';
	}
}


class UserTimelineTweetQuery extends AbstractTweetQuery 
{
	protected $screen_name;

	public function __construct($screen_name, $start_from_tweet_id)
	{
		parent::__construct($start_from_tweet_id);
		$this->setScreenName($screen_name);
	}

	public function setScreenName($screen_name)
	{
		$this->screen_name = $screen_name;
	}

	public function getScreenName()
	{
		return $this->screen_name;
	}

	public function getMethod() 
	{
		return TweetQueryMethod::USER_TIMELINE;
	}

	public function getTweets($twitter_connection, $max_tweets_returned)
	{
		$parameters = array(
			'screen_name' => $this->getScreenName(),
			'count' => $max_tweets_returned,
		);


		if ($this->getStartFromTweetID() !== null) {
			$parameters['since_id'] = $this->getStartFromTweetID();
		}

		return $twitter_connection->get('statuses/user_timeline', $parameters);
	}

	public function getTitle()
	{
		return 'Twitter User Timeline Query for ' . $this->getScreenName();
	}
}


// Stuff to convert tweets
class TweetToPostConverter
{
	protected $id;
	protected $tweet_query;
	protected $tag_for_post;

	public function __construct($id, $tweet_query, $tag_for_post)
	{
		$this->setID($id);
		$this->setTweetQuery($tweet_query);
		$this->setTagForPost($tag_for_post);
	}

	public function setID($id)
	{
		$this->id = $id;
	}

	public function getID()
	{
		return $this->id;
	}

	public function setTweetQuery($tweet_query)
	{
		$this->tweet_query = $tweet_query;
	}

	public function getTweetQuery()
	{
		return $this->tweet_query;
	}

	public function setTagForPost($tag_for_post)
	{
		$this->tag_for_post = $tag_for_post;
	}

	public function getTagForPost()
	{
		return $this->tag_for_post;
	}
	
}


class TweetToPostConverterManager
{
	public static function update_converters_from_admin_page($converter_options)
	{
		$existing_converters = get_option('dg_tw_converters', array());		
		$new_converters = array();
		$updated_converters = array();
		
		foreach($converter_options as $item_query)
		{
			if(array_key_exists($item_query['id'], $existing_converters))
			{
				// Pre-existing...see if it has changed...
				$converter = $existing_converters[$item_query['id']];
				$query = $converter->getTweetQuery();

				// If the method changed, make a new query
				if ($item_query['method'] != $query->getMethod()) {
					if ($item_query['method'] == TweetQueryMethod::SEARCH) {
						$query = new SearchTweetQuery($item_query['value']);
					}
					else {
						$query = new UserTimelineTweetQuery($item_query['value']);
					}
				}
				// If the screen_name or search_query changed, make a new query
				else if ($query->getMethod() == TweetQueryMethod::SEARCH && $query->getSearchQuery() != $item_query['value'])
				{
					$query = new SearchTweetQuery($item_query['value']);
				}
				else if ($query->getMethod() == TweetQueryMethod::USER_TIMELINE && $query->getScreenName() != $item_query['value'])
				{
					$query = new UserTimelineTweetQuery($item_query['value']);
				}

				// Update the query
				$converter->setTweetQuery($query);

				// Update the tag
				$converter->setTagForPost($item_query['tag']);

				// Copy over to new array
				$updated_converters[$converter->getID()] = $converter;
			}
			else
			{
				// Create a new converter...
				if ($item_query['method'] == TweetQueryMethod::SEARCH) {
					$query = new SearchTweetQuery($item_query['value']);
				}
				else {
					$query = new UserTimelineTweetQuery($item_query['value']);
				}
				array_push($new_converters, new TweetToPostConverter(null, $query, $item_query['tag']));
			}
		}		

		// Give id's to new converters
		$next_id = 0;
		foreach($new_converters as $converter) {
			while(array_key_exists($next_id, $updated_converters)) {
				$next_id++;
			}
			$converter->setID($next_id);
			$updated_converters[$converter->getID()] = $converter;
		}

		// Update!
		update_option('dg_tw_converters', $updated_converters);
	}
}

?>
