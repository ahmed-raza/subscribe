<?php
/**
 * @file
 * Contains \Drupal\subscribe\Form\ConfigForm.
 */

namespace Drupal\subscribe\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;

class SubscribeForm extends FormBase {
  /**
  *   {@inheritdoc}
  */
  public function getFormId(){
    return 'subscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    $form = array();

    $form['mail_token'] = array(
      '#type' => 'hidden',
      '#value' => session_id(),
      );

    if ($this->getConfig('form_description')) {
      $form['markup'] = array(
        '#type'=>'markup',
        '#markup'=>$this->getConfig('form_description'),
        '#prefix'=>'<div class="markup">',
        '#suffix'=>'</div>'
        );
    }

    if ($this->getConfig('username_field')) {
      $form['username'] = array(
        '#type'=>'textfield',
        '#title'=>'Name',
        '#required'=>true
        );
    }

    $form['email'] = array(
      '#type'=>'email',
      '#title'=>'Email',
      '#required'=>true,
      '#suffix'=>'<div class="email-error-message"></div>',
      );

    $form['actions']['submit'] = array(
      '#type'=>'submit',
      '#value'=>'Subscribe',
      '#attributes'=>array(
        'class'=>['button--primary']
        ),
      );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $table = 'subscribe_subscribers';
    $query = db_select($table, 'subscriber')
            ->fields('subscriber')
            ->condition('email',$email,'=')
            ->condition('status',1)
            ->execute()
            ->fetchAssoc();
    if ($query) {
      $form_state->setErrorByName('email', $this->t('Email already exists.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Storing form data to database.
    $token = $form_state->getValue('mail_token');
    $username = $form_state->getValue('username');
    $email = $form_state->getValue('email');
    if ($this->confirmedExists($email)) {
      $this->sendMail($token, $username, $email, $resend = false, $remove = true);
      drupal_set_message($this->t('This email already subscribed, an email has been dispatched to this address with unsubscription  link.'));
    }else if($this->unConfirmedExists($email)){
      $this->updateSubscriber($token, $username, $email);
      $this->sendMail($token, $username, $email, $resend = true, $remove = false);
      drupal_set_message($this->t('Seems like the email already subscribed but is not confirmed, an email has been dispatched to this address with confirmation link.'));
    }else if ($this->storeValues($username, $email)) {
      drupal_set_message($this->t('You have successfully subscribed to our newsletter.'));
      $this->sendMail($token, $username, $email, $resend = false, $remove = false);
    }else{
      drupal_set_message($this->t('Error'), 'error');
    }
  }

  private function storeValues($username, $email){
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

  private function updateSubscriber($token, $username, $email){
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
              ->condition('status', 0, '=')
              ->execute();
    return $update;
  }

  private function getConfig($field){
    $table = 'subscribe_config';
    $query = db_select($table, 'config')
            ->fields('config')
            ->condition('id', 1, '=')
            ->execute()
            ->fetchAssoc();
    return $query[$field];
  }

  private function sendMail($token, $username, $email, $resend, $remove){
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

  private function confirmedExists($email){
    $table = 'subscribe_subscribers';
    $check = db_select($table, 'subscriber')
            ->fields('subscriber')
            ->condition('email', $email, '=')
            ->condition('status', 1, '=')
            ->execute()
            ->fetchAssoc();
    return $check;
  }

  private function unConfirmedExists($email){
    $table = 'subscribe_subscribers';
    $check = db_select($table, 'subscriber')
            ->fields('subscriber')
            ->condition('email', $email, '=')
            ->condition('status', 0, '=')
            ->execute()
            ->fetchAssoc();
    return $check;
  }
}
