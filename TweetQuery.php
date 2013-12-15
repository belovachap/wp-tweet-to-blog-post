<?php

abstract class TweetQueryMethod
{
	const SEARCH = 0;
	const USER_TIMELINE = 1;

	public $QUERY_METHOD_NAMES = array(
		SEARCH => "Twitter Search",
		USER_TIMELINE => "User Timeline",
	);
}


abstract class AbstractTweetQuery
{
	private $start_from_tweet_id;
	private $max_tweets_returned;

	public function __construct($start_from_tweet_id, $max_tweets_returned)
	{
		$this->setStartFromTweetID($start_from_tweet_id);
		$this->setMaxTweetsReturned($max_tweets_returned);
	}

	public function setStartFromTweetID($tweet_id)
	{
		$this->start_from_tweet_id = $tweet_id;
	}

	public function getStartFromTweetID()
	{
		return $this->start_from_tweet_id;
	}

	public function setMaxTweetsReturned($max_tweets_returned)
	{
		$this->max_tweets_returned = $max_tweets_returned;
	}

	public function getMaxTweetsReturned()
	{
		return $this->max_tweets_returned;
	}

	abstract public function getMethod();
	abstract public function getTweets($twitter_connection);
}


class SearchTweetQuery extends AbstractTweetQuery 
{
	private $search_query;

	public function __construct($search_query, $start_from_tweet_id, $max_tweets_returned)
	{
		parent::__construct($start_from_tweet_id, $max_tweets_returned);
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

	public function getTweets($twitter_connection)
	{
		$search_parameters = array(
			'q' => $this->getSearchQuery(),
			'since_id' => $this->getStartFromTweetID(),
			'include_entities' => true,
			'count' => $this->getMaxTweetsReturned(),
		);

		$api_result = $twitter_connection->get('search/tweets', $search_parameters);

		return $api_result->statuses;
	}
}


class UserTimelineTweetQuery extends AbstractTweetQuery 
{
	private $screen_name;

	public function __construct($screen_name, $start_from_tweet_id, $max_tweets_returned)
	{
		parent::__construct($start_from_tweet_id, $max_tweets_returned);
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

	public function getTweets($twitter_connection)
	{
		// Retrieve tweets from screen_name's timeline
		return;
	}
}

?>
