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
use Drupal\subscribe\Subscribe;

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

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validating email address
    if (!valid_email_address($form_state->getValue('email'))) {
      $form_state->setErrorByName('email', $this->t('The email is not valid.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Storing form data to database.
    $username = '';
    if ($this->getConfig('username_field')) {
      $username = $form_state->getValue('username');
    }
    $token = $form_state->getValue('mail_token');
    $email = $form_state->getValue('email');
    if (Subscribe::confirmedExists($email)) {
      Subscribe::updateSubscriber($token, $username, $email);
      Subscribe::sendMail($token, $username, $email, $resend = false, $remove = true);
      drupal_set_message($this->t('This email already subscribed, an email has been dispatched to this address with unsubscription  link.'));
    }else if(Subscribe::unConfirmedExists($email)){
      Subscribe::updateSubscriber($token, $username, $email);
      Subscribe::sendMail($token, $username, $email, $resend = true, $remove = false);
      drupal_set_message($this->t('Seems like the email already subscribed but is not confirmed, an email has been dispatched to this address with confirmation link.'));
    }else if (Subscribe::subscribe($username, $email)) {
      drupal_set_message($this->t('You have successfully subscribed to our newsletter.'));
      Subscribe::sendMail($token, $username, $email, $resend = false, $remove = false);
    }else{
      drupal_set_message($this->t('Error'), 'error');
    }
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
}
