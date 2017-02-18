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

class OperationsForm extends FormBase {
  /**
  *   {@inheritdoc}
  */
  public function getFormId(){
    return 'subscribe_operations_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state){    
    $form = array();

    $form['operations'] = array(
      '#type'=>'details',
      '#title'=>t('Operations'),
      '#collapsible'=>true,
      '#collapsed'=>true,
      );

    $form['operations']['actions']['export'] = array(
      '#type'=>'submit',
      '#value'=>'Export All Subscribers',
      '#attributes'=>array(
        'class'=>['button--primary']
        ),
      '#markup'=>'<div class="export-desc">'.$this->t('Exports all subscribers to <em>.xls</em> file.').'</div>',
      );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Storing form data to database.
    $subscribers = db_select('subscribe_subscribers', 'emails')
            ->fields('emails')
            ->condition('status', 1, '=')
            ->execute()
            ->fetchAllAssoc('sid');
    $emails = [];
    foreach ($subscribers as $key => $value) {
      $emails[$key]['name'] = $value->username;
      $emails[$key]['email'] = $value->email;
    }
    $batch = array(
      'title'=>t('Deleting Subscribers..'),
      'operations'=>array(
        array(
          '\Drupal\subscribe\Export::exportSubscribers',
          array($emails),
          ),
        ),
      'finished'=>'\Drupal\subscribe\Export::finished',
      );
    batch_set($batch);
  }
}
