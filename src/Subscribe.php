<?php

namespace Drupal\subscribe;
use Drupal\file\Entity\File;
use Drupal\Core\Mail\MailManagerInterface;

class Subscribe {

  public function subscribe($username, $email){
    $data = array(
      'username'=>$username,
      'email'=>$email,
      'status'=>0,
      'token'=>session_id()
      );
    $table = 'subscribe_subscribers';    
    $insert = db_insert($table)
            ->fields($data)
            ->execute();
    return true;
  }

  public function updateSubscriber($token, $username, $email){
    $table = 'subscribe_subscribers';
    $data = array(
      'username'=>$username,
      'email'=>$email,
      'status'=>0,
      'token'=>session_id()
      );
    $update = db_update($table)
              ->fields($data)
              ->condition('email', $email, '=')
              ->execute();
    return $update;
  }

  public function updateStatus($token){
    $data = array(
      'status'=>1,
      );
    $table = 'subscribe_subscribers';
    $update = db_update($table)
            ->fields($data)
            ->condition('token', $token, '=')
            ->execute();
    return $update;
  }

  public function confirmedExists($email){
    $table = 'subscribe_subscribers';
    $check = db_select($table, 'subscriber')
            ->fields('subscriber')
            ->condition('email', $email, '=')
            ->condition('status', 1, '=')
            ->execute()
            ->fetchAssoc();
    return $check;
  }

  public function unConfirmedExists($email){
    $table = 'subscribe_subscribers';
    $check = db_select($table, 'subscriber')
            ->fields('subscriber')
            ->condition('email', $email, '=')
            ->condition('status', 0, '=')
            ->execute()
            ->fetchAssoc();
    return $check;
  }

  public function sendMail($token, $username, $email, $resend, $remove){
    global $base_url;
    $mailManager = \Drupal::service('plugin.manager.mail');
    if ($remove) {
      $module = 'subscribe';
      $key = 'subscribe_remove';
      $to = $email;
      $params['email'] = $email;
      $params['name'] = $username;
      $params['token'] = $token;
      $params['link'] = $base_url.'/subscribe/remove?token='.$token;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;

      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if ($result['result'] !== true) {
        \Drupal::logger('subscribe-logger')->notice($this->t('Failed to send removal email.'));
      }
    }else if($resend){
      $module = 'subscribe';
      $key = 'subscribe_resend';
      $to = $email;
      $params['email'] = $email;
      $params['name'] = $username;
      $params['token'] = $token;
      $params['link'] = $base_url.'/subscribe/confirm?token='.$token;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;

      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if ($result['result'] !== true) {
        \Drupal::logger('subscribe-logger')->notice($this->t('Failed to send duplicate confirmation email.'));
      }
    }else{
      $module = 'subscribe';
      $key = 'subscribe_submit';
      $to = $email;
      $params['email'] = $email;
      $params['name'] = $username;
      $params['token'] = $token;
      $params['link'] = $base_url.'/subscribe/confirm?token='.$token;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;

      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if ($result['result'] !== true) {
        \Drupal::logger('subscribe-logger')->notice($this->t('Failed to send confirmation email.'));
      }
    }
  }

}
