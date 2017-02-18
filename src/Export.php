<?php

namespace Drupal\subscribe;
use Drupal\file\Entity\File;

class Export {

  public static function exportSubscribers($emails, &$context){
    $file = File::create([
      'uid' => 1,
      'filename' => 'export.xls',
      'uri' => 'public://export/export.xls',
      'status' => 1,
    ]);
    $file->save();
    $message = 'Exporting...';
    $results = array();
    foreach ($emails as $email) {
      $results[] = $email['name']. ";" . $email['email']."\n";
    }
    file_put_contents($file->getFileUri(), $results);
    $file->save();
    $file_usage = \Drupal::service('file.usage');
    $file_usage->add($file, 'subscribe', 'user', 1);
    $file->save();
    $context['message'] = $message;
    $context['results'] = $results;
  }

  function finished($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One item processed.', '@count items processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }
}
