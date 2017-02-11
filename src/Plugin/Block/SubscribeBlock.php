<?php
/**
 * @file
 * Contains \Drupal\subscribe\Plugin\Block\CheckoutBlock.
 */
namespace Drupal\subscribe\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'checkout' block.
 *
 * @Block(
 *   id = "subscribe_block",
 *   admin_label = @Translation("Subscribe block"),
 *   category = @Translation("Subscribe form as a block")
 * )
 */

class SubscribeBlock extends BlockBase {
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\subscribe\Form\SubscribeForm');
  }
}
