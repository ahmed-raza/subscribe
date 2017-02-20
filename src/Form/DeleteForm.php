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

class DeleteForm extends FormBase {
  /**
  *   {@inheritdoc}
  */
  public function getFormId(){
    return 'subscribe_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = null){
    $form = array();

    $form['markup'] = array(
      '#type'=>'markup',
      '#markup'=>$this->t('Are you sure you want to delete this subscriber?'),
      '#prefix'=>'<p>',
      '#suffix'=>'</p>',
      );

    $form['id'] = array(
      '#type'=>'hidden',
      '#value'=>$id,
      );

    $form['actions']['submit'] = array(
      '#type'=>'submit',
      '#value'=>$this->t('Delete'),
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
    if (empty($form_state->getValue('id'))) {
      drupal_set_message($this->t('User not found.'), 'error');
      $form_state->setRedirect('subscribe.subscribers');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Storing form data to database.
    if ($this->delete($form_state->getValue('id'))) {
      drupal_set_message($this->t('subscriber deleted.'));
      $form_state->setRedirect('subscribe.subscribers');
    }else{
      drupal_set_message($this->t('User not found.'), 'error');
      $form_state->setRedirect('subscribe.subscribers');
    }
  }

  private function delete($id){
    $table = 'subscribe_subscribers';
    $delete = db_delete($table)
            ->condition('sid', $id, '=')
            ->execute();
    return $delete;
  }
}
