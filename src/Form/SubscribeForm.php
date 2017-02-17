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
    $username = $form_state->getValue('username');
    $email = $form_state->getValue('email');
    if ($this->storeValues($username, $email)) {
      drupal_set_message($this->t('You have successfully subscribed to our newsletter.'));
      $token = $form_state->getValue('mail_token');
      $username = $form_state->getValue('username');
      $email = $form_state->getValue('email');
      $this->sendMail($token, $username, $email);
    }else{
      drupal_set_message($this->t('Error'), 'error');
    }
  }

  private function storeValues($username_field, $form_description){
    $data = array(
      'username'=>$username_field,
      'email'=>$form_description,
      'status'=>0,
      'token'=>session_id()
      );
    $table = 'subscribe_subscribers';    
    $insert = db_insert($table)
            ->fields($data)
            ->execute();
    return true;
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

  private function sendMail($token, $username, $email){
    global $base_url;
    $mailManager = \Drupal::service('plugin.manager.mail');
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
      \Drupal::logger('subscribe-logger')->notice($message);
    }
  }
}
