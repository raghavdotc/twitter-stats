<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TweetedUrl
 *
 * @author Raghav
 */
class TweetedUrl extends AppModel {

    public $belongsTo = array(
        'Tweet' => array(
            'className' => 'Tweet',
            'foreignKey' => 'tweet_id'
        )
    );

}

?>
