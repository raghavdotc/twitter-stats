<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tweet
 *
 * @author Raghav
 */
class Tweet extends AppModel {

    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id'
        )
    );
    public $hasMany = array(
        'TweetedUrl' => array(
            'className' => 'TweetedUrl',
            'foreignKey' => 'tweet_id'
        )
    );

    public function save_timeline($tweets, $user_id) {

        $today = new DateTime("now", new DateTimeZone(date_default_timezone_get()));
        $least_tweet_id = false;
        $users = array();
        $tweet_model = array();
        foreach ($tweets as $tweet) {
            if (isset($tweet->entities->urls) && count($tweet->entities->urls) > 0) {
                $least_tweet_id = number_format($tweet->id, 0, '', '');
                $date_parts = explode(" ", $tweet->created_at);
                $unixtimestamp = strtotime($date_parts[2] . '-' . $date_parts[1] . ' ' . $date_parts[5]);
                $dateObj = new DateTime();
                $dateObj->setTimestamp($unixtimestamp);
                $diff = $today->diff($dateObj);
                if ($diff->days > 5) {
                    $least_tweet_id = false;
                    break;
                }
                if (!array_key_exists($tweet->user->screen_name, $users)) {
                    $users[$tweet->user->screen_name] = array('name' => $tweet->user->name, 'handle' => $tweet->user->screen_name);
                    $existing_user = $this->User->findByHandle($tweet->user->screen_name);
                    if (empty($existing_user)) {
                        $this->User->create();
                        $this->User->save($users[$tweet->user->screen_name]);
                        $users[$tweet->user->screen_name]['user_id'] = $this->User->id;
                    } else {
                        $users[$tweet->user->screen_name]['user_id'] = $existing_user['User']['id'];
                    }
                }
                $this->create();
                $this->save(array(
                    'tweeter_id' => $user_id,
                    'tweet' => $tweet->text,
                    'user_id' => $users[$tweet->user->screen_name]['user_id'],
                    'tweet_twitter_id' => $least_tweet_id
                ));

                $tweeted_urls = array();
                foreach ($tweet->entities->urls as $url) {
                    $domain = substr($url->expanded_url, 0, strpos($url->expanded_url, '/', 7) - 7);
                    $tweeted_urls[] = array(
                        'tweet_id' => $this->id,
                        'url' => $url->expanded_url,
                        'shortened_url' => $url->url,
                        'domain' => $domain
                    );
                }
                $this->TweetedUrl->saveMany($tweeted_urls);
            }
        }
        return $least_tweet_id;
    }

    public function get_last_tweet_id($user_id) {
        $tweet = $this->find('first', array(
            'conditions' => array(
                'user_id' => $user_id
            ),
            'recursive' => -1,
            'order' => array('Tweet.id' => 'DESC')
        ));
        if (empty($tweet)) {
            return false;
        }
        return $tweet['Tweet']['tweet_twitter_id'];
    }

}

?>
