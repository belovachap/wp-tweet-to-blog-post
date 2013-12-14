<?php

interface iTweetQuery
{
	public function setQuery($query);
	public function getQuery();

	public function setMethod($method);
	public function getMethod();

	public function setStartFromTweetID($start_from_tweet_id);
	public function getStartFromTweetID();

	public function setMaxTweetsReturned($max_tweets_returned);
	public function getMaxTweetsReturned();

	public function getTweets($twitter_connetion);
}

abstract class TweetQueryMethod
{
	const SEARCH = 0;
	const USER_TIMELINE = 1;

/*
	public static name_for_query_method($query_method) {
		switch($query_method)
	QUERY_METHOD_NAMES = array(
		SEARCH => "Search",
		USER_TIMELINE => "User Timeline",
	);
*/
}

class SearchTweetQuery implements iTweetQuery 
{
	private $query;
	private $method;
	private $start_from_tweet_id;
	private $max_tweets;

	public function __construct($query, $method, $start_from_tweet_id, $max_tweets_returned)
	{
		$this->setQuery($query);
		$this->setMethod($method);
		$this->setStartFromTweetID($start_from_tweet_id);
		$this->setMaxTweetsReturned($max_tweets_returned);
	}

	public function setQuery($query)
	{
		$this->query = $query;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function getMethod() 
	{
		return $this->method;
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

	public function getTweets($twitter_connection)
	{
		$search_parameters = array(
			'q' => $this->getQuery(),
			'since_id' => $this->getStartFromTweetID,
			'include_entities' => true,
			'count' => $this->getMaxTweetsReturned(),
		);

		$api_result = $twitter_connection->get('search/tweets', $search_parameters);

		return $api_result->statuses;
	}
}

?>
