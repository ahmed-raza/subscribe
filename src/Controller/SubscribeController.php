<?php 

namespace Drupal\subscribe\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class SubscribeController extends ControllerBase {
  public function config(){
    return "Hello";
  }
}
