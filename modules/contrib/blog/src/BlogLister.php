<?php

/**
 * @file
 * Contains \Drupal\book\BlogLister.
 */

namespace Drupal\blog;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;
use Drupal\Component\Utility\Xss;

/**
 * Defines a blog lister.
 */
class BlogLister implements BlogListerInterface {

  /**
   * Config Factory Service Object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a BlogLister object.
   */
  public function __construct(AccountInterface $account, ConfigFactoryInterface $config_factory) {
    $this->account = $account;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   *
   * @param UserInterface $user
   *   User object
   *
   * @return String
   *   Title string
   */
  public function userBlogTitle(UserInterface $user) {
    return Xss::filter($user->getUsername()) . "'s blog";
  }

}
