<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author Raghav
 */
class User extends AppModel {

    public $hasMany = array(
        'Tweet' => array(
            'className' => 'Tweet',
            'foreignKey' => 'user_id'
        )
    );

    public function get_user_id($user_data, $accessToken) {
        $user = $this->findByHandle($user_data->screen_name);
        if (empty($user)) {
            $this->create();
        } else {
            $this->id = $user['User']['id'];
        }
        $user_model = array(
            'request_token_key' => $accessToken->key,
            'request_token_secret' => $accessToken->secret,
            'name' => $user_data->name,
            'handle' => $user_data->screen_name
        );
        $this->save($user_model);
        return $this->id;
    }

}

?>
