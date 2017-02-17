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
use Drupal\Core\Url;
use \Drupal\Core\Link;

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
    $param = \Drupal::request()->query->all();
    $form = array();

    $form['#method'] = 'get';

    $form['filteration'] = array(
      '#type'=>'details',
      '#title'=>t('Filter'),
      '#collapsible'=>true,
      '#collapsed'=>true,
      );

    $form['filteration']['username'] = array(
      '#type'=>'textfield',
      '#title'=>'Username',
      '#default_value'=>$param['username']
      );

    $form['filteration']['email'] = array(
      '#type'=>'email',
      '#title'=>'Email',
      '#default_value'=>$param['email']
      );

    $form['filteration']['status'] = array(
      '#type'=>'select',
      '#title'=>'Status',
      '#options'=>array(
          1=>'Yes',
          0=>'No'
        ),
      '#default_value'=>$param['status']
      );

    $form['filteration']['actions']['submit'] = array(
      '#type'=>'submit',
      '#value'=>'Filter',
      '#attributes'=>array(
        'class'=>['button--primary']
        ),
      );

    $form['filteration']['actions']['reset'] = array(
      '#type'=>'markup',
      '#markup'=>Link::fromTextAndUrl('Reset', Url::fromRoute('subscribe.subscribers'))->toString()
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
