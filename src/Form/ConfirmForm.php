<?php
/**
 * @file
 * Contains \Drupal\subscribe\Form\ConfigForm.
 */

namespace Drupal\subscribe\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Html;
use Drupal\subscribe\Subscribe;

class ConfirmForm extends FormBase {
  /**
  *   {@inheritdoc}
  */
  public function getFormId(){
    return 'subscribe_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    $param = \Drupal::request()->query->all();

    $form = array();

    $form['markup'] = array(
      '#type'=>'markup',
      '#markup'=>$this->t('Are you sure you want to confirm subscription?'),
      '#prefix'=>'<p>',
      '#suffix'=>'</p>',
      );

    $form['mail_token'] = array(
      '#type'=>'hidden',
      '#value'=>$param['token'],
      );

    $form['actions']['submit'] = array(
      '#type'=>'submit',
      '#value'=>'Confirm',
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
    if (empty($form_state->getValue('mail_token'))) {
      drupal_set_message($this->t('Token not found or expired.'), 'error');
      $form_state->setRedirect('view.frontpage.page_1');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Storing form data to database.
    if (Subscribe::updateStatus($form_state->getValue('mail_token'))) {
      drupal_set_message($this->t('Successfully confirmed the email.'));
      $form_state->setRedirect('view.frontpage.page_1');
    }else{
      drupal_set_message($this->t('Email not found.'), 'error');
      $form_state->setRedirect('view.frontpage.page_1');
    }
  }
}
