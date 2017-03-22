<?php 

namespace Drupal\subscribe\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use \Drupal\Core\Link;

class SubscribeController extends ControllerBase {
  public function subscribers(){
    $param = \Drupal::request()->query->all();
    $filterForm = \Drupal::formBuilder()->getForm('Drupal\subscribe\Form\FilterForm');
    $operationsForm = \Drupal::formBuilder()->getForm('Drupal\subscribe\Form\OperationsForm');
    
    $build = array(
      '#type' => 'markup',
      '#markup' => t('List of subscribers.'),
    );

    $header = array(
      'name' => t('Name'),
      'email' => t('Email'),
      'confirmed' => t('Confirmed'),
      'time' => t('Created'),
      'actions' => t('Actions'),
      );

    $rows = array();

    $results = $this->getSubscribers();
    foreach ($results as $key => $value) {
      $row = array();
      $created = strtotime($value->created);
      $row['name'] = $value->username;
      $row['email'] = $value->email;
      $row['confirmed'] = ($value->status) ? 'Yes' : 'No';
      $row['time'] = date('d-M-Y', $created);
      $operations['delete'] = array(
        'title'=>t('Delete'),
        'url'=>Url::fromRoute('subscribe.delete', ['id'=>$key]),
        );
      $row['operations'] = array(
        'data' => array(
          '#type' => 'operations',
          '#links' => $operations,
        ),
      );
      $rows[] = $row;
    }
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => array(
        'id' => 'subscribers-listing-table',
        ),
      );

    $final = array($filterForm, $operationsForm, $build, $table);

    return $final;
  }

  private function getSubscribers(){
    $param = \Drupal::request()->query->all();
    $table = 'subscribe_subscribers';
    if (!empty($param)) {
      $or = db_or($table)
              ->condition('username',$param['username'],'LIKE')
              ->condition('email',$param['email'],'=')
              ->condition('status',$param['status'],'=');
      $query = db_select($table, 'subscribers')
              ->fields('subscribers')
              ->condition($or)
              ->execute()
              ->fetchAllAssoc('sid');
      return $query;
    }else{
      $query = db_select($table, 'subscribers')->fields('subscribers')->execute()->fetchAllAssoc('sid');
      return $query;
    }
  }
}
