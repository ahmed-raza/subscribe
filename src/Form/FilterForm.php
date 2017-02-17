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

class FilterForm extends FormBase {
  /**
  *   {@inheritdoc}
  */
  public function getFormId(){
    return 'subscribe_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    $form = array();

    $form['filteration'] = array(
      '#type'=>'details',
      '#title'=>t('Filter'),
      '#collapsible'=>true,
      '#collapsed'=>true,
      );

    $form['filteration']['username'] = array(
      '#type'=>'textfield',
      '#title'=>'Username',
      );

    $form['filteration']['email'] = array(
      '#type'=>'email',
      '#title'=>'Email',
      );

    $form['filteration']['status'] = array(
      '#type'=>'select',
      '#title'=>'Status',
      '#options'=>array(
          1=>'Yes',
          0=>'No'
        ),
      );

    $form['filteration']['actions']['submit'] = array(
      '#type'=>'submit',
      '#value'=>'Filter',
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
  }
}
