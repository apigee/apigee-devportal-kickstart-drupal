<?php

namespace Drupal\apigee_kickstart_migrate\Plugin\migrate\source;

use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * @MigrateSource(
 *   id = "devportal_node"
 * )
 */
class DevportalNode extends Node {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->innerJoin('users', 'u', 'n.uid = u.uid');
    $query->addField('u', 'mail', 'user_mail');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields =  parent::fields();
    $fields['user_mail'] = $this->t('The email of the user');
    return $fields;
  }

}
