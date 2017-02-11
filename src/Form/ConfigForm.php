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

class ConfigForm extends FormBase {
  /**
  *   {@inheritdoc}
  */
  public function getFormId(){
    return 'subscribe_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    $form = array();

    $form['username_field'] = array(
      '#type'=>'checkbox',
      '#title'=>'Show username field',
      '#default_value'=>$this->getDefault('username_field'),
      );

    $form['form_description'] = array(
      '#type'=>'textarea',
      '#title'=>'Description',
      '#default_value'=>$this->getDefault('form_description'),
      );

    $form['actions']['submit'] = array(
      '#type'=>'submit',
      '#value'=>'Save',
      '#attributes'=>array(
        'class'=>['button--primary']
        ),
      );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Storing form data to database.
    $username_field = $form_state->getValue('username_field');
    $form_description = $form_state->getValue('form_description');
    if ($this->storeValues($username_field, $form_description)) {
      drupal_set_message($this->t('Configurations saved.'));
    }else{
      drupal_set_message($this->t('Error'), 'error');
    }
  }

  private function storeValues($username_field, $form_description){
    $data = array(
      'username_field'=>$username_field,
      'form_description'=>$form_description,
      );
    $table = 'subscribe_config';
    $query = db_select($table, 'config')
            ->fields('config')
            ->execute()
            ->fetchAssoc();
    if ($query) {
      $update = db_update($table)
              ->fields($data)
              ->condition('id', 1, '=')
              ->execute();
      return true;
    }else{
      $insert = db_insert($table)
              ->fields($data)
              ->execute();
      return true;
    }
  }

  private function getDefault($field){
    $table = 'subscribe_config';
    $query = db_select($table, 'config')
            ->fields('config')
            ->condition('id', 1, '=')
            ->execute()
            ->fetchAssoc();
    return $query[$field];
  }
}
