<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TwitterController
 *
 * @author Raghav
 */
App::import('Vendor', 'OAuth/OAuthClient');

class TwitterController extends AppController {

    public $uses = array('User', 'Tweet', 'TweetedUrl');

    public function login() {
        $client = $this->_create_twitter_client();
        $requestToken = $client->getRequestToken('https://api.twitter.com/oauth/request_token', $_SERVER['SERVER_NAME'] . '/twitter/callback');
        if ($requestToken) {
            $this->Session->write('twitter_request_token', $requestToken);
            $this->redirect('https://api.twitter.com/oauth/authorize?oauth_token=' . $requestToken->key);
        }
    }

    private function _create_twitter_client() {
        return new OAuthClient('MqUZpPUSXCz9kkrySk6odA', 'I9Reo8SXMBNQR3hw6xszZNd3eUwrAVbGh0zFsDDLlY');
    }

    public function callback() {
        $accessToken = $this->_get_access_token();
        $user_data = $this->_get_user_data($accessToken);
        if (!isset($user_data->errors)) {
            $user_id = $this->User->get_user_id($user_data, $accessToken);
            $this->Session->write('user_id', $user_id);
            $this->Session->write('oauth_access', $accessToken);
        } else {
            $this->set("response", "Please come back tomo, You have visited us one too many times, for today!");
        }
    }

    public function see_statistics() {
        if (!$this->Session->check('user_id')) {
            $this->redirect('/');
        }
        $user_id = $this->Session->read('user_id');
        $tweets = $this->Tweet->find('all', array(
            'conditions' => array(
                'Tweet.tweeter_id' => $user_id
            ),
            'limit' => 50,
            'order' => array(
                'Tweet.tweet_twitter_id' => 'DESC'
            )
        ));
        $this->set('tweets', $tweets);

        $tweet_count = $this->Tweet->query("SELECT count(*) as number_tweets, User.handle FROM tweets Tweet
                                INNER JOIN users User on Tweet.user_id = User.id
                                GROUP BY Tweet.user_id ORDER BY number_tweets DESC");
        if (!empty($tweet_count)) {
            $this->set('tweet_count', $tweet_count);
        } else {
            $this->set('tweet_count', false);
        }

        $domain_count = $this->TweetedUrl->query("SELECT count(*) as number_tweets, TweetedUrl.domain FROM tweeted_urls TweetedUrl
                                                    GROUP by domain ORDER BY number_tweets DESC");
        if (!empty($domain_count)) {
            $this->set('domain_count', $domain_count);
        } else {
            $this->set('domain_count', false);
        }
    }

    public function fetch_next_50($offset = null) {
        $this->layout = 'ajax';
        $this->autoLayout = false;
        $this->autoRender = false;
        $user_id = $this->Session->read('user_id');
        $tweets = $this->Tweet->find('all', array(
            'conditions' => array(
                'Tweet.tweeter_id' => $user_id
            ),
            'limit' => 50,
            'offest' => $offset != null ? $offset : 0
        ));
        if (count($tweets) > 0) {
            $response['status'] = true;
            $response['tweets'] = $tweets;
        } else {
            $response['status'] = false;
        }
        $this->set('response', $response);
        $this->render('/Twitter/get_user_timeline_before_id');
    }

    public function get_user_timeline_before_id($id = null) {
        $this->layout = 'ajax';
        $this->autoLayout = false;
        $this->autoRender = false;
        if (!$this->Session->check('user_id')) {
            $response['state'] = false;
        } else {
            if ($id == null) {
                $id = $this->Tweet->get_last_tweet_id($this->Session->read('user_id'));
            }
            if ($id != null) {
                $options['max_id'] = $id;
            }
            $options['count'] = 50;
            $user_id = $this->Session->read('user_id');
            $accessToken = $this->Session->read('oauth_access');
            $tweets = $this->_get_user_timeline($accessToken, $options);
            $last_tweet_id = $this->Tweet->save_timeline($tweets, $user_id);
            $response['state'] = true;
            $response['last_tweet_id'] = $last_tweet_id;
        }
        $this->set('response', $response);
        $this->render('/Twitter/get_user_timeline_before_id');
    }

    public function logout() {
        $this->Session->destroy();
        $this->redirect('/');
    }

    private function _get_user_data($accessToken) {
        $client = $this->_create_twitter_client();
        return json_decode($client->get($accessToken->key, $accessToken->secret, 'https://api.twitter.com/1.1/account/verify_credentials.json'));
    }

    private function _get_access_token() {
        $requestToken = $this->Session->read('twitter_request_token');
        $client = $this->_create_twitter_client();
        $accessToken = $client->getAccessToken('https://api.twitter.com/oauth/access_token', $requestToken);
        if ($accessToken)
            return $accessToken;
        else
            return false;
    }

    private function _get_user_timeline($accessToken, $options = array()) {
        $query = "";
        if (!empty($options)) {
            foreach ($options as $key => $option) {
                $option_strs[] = $key . "=" . (string) $option;
            }
            $query = implode('&', $option_strs);
        }
        $url = 'https://api.twitter.com/1.1/statuses/home_timeline.json?' . $query;
        $client = $this->_create_twitter_client();
        return json_decode($client->get($accessToken->key, $accessToken->secret, $url));
    }

}

?>
