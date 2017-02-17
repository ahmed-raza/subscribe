<?php 

namespace Drupal\subscribe\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class SubscribeController extends ControllerBase {
  public function subscribers(){
    $form = \Drupal::formBuilder()->getForm('Drupal\subscribe\Form\FilterForm');
    $build = array(
      '#type' => 'markup',
      '#markup' => t('List of subscribers.'),
    );

    $header = array(
      'id' => t('ID'),
      'name' => t('Name'),
      'email' => t('Email'),
      'confirmed' => t('Confirmed'),
      'time' => t('Created'),
      );

    $rows = array();

    $results = $this->getSubscribers();
    foreach ($results as $key => $value) {
      $created = strtotime($value->created);
      $rows[] = array(
        'data'=>array(
          $key,
          $value->username,
          $value->email,
          ($value->status) ? 'Yes' : 'No',
          date('d-M-Y', $created)
          )
        );
    }
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => array(
        'id' => 'subscribers-listing-table',
        ),
      );

    $final = array($form, $build, $table);

    return $final;
  }

  private function getSubscribers(){
    $table = 'subscribe_subscribers';
    $query = db_select($table, 'subscribers')->fields('subscribers')->execute()->fetchAllAssoc('sid');
    return $query;
  }
}
