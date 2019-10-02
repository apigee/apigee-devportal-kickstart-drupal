<?php

namespace Drupal\apigee_kickstart_migrate\Plugin\migrate\source;

use Drupal\comment\Plugin\migrate\source\d7\Comment;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "devportal_comment"
 * )
 */
class DevportalComment extends Comment {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->innerJoin('users', 'u', 'c.uid = u.uid');
    $query->addField('u', 'mail', 'user_mail');
    return $query;
  }

  public function prepareRow(Row $row) {
    $comment_type = $row->getSourceProperty('node_type') === 'forum' ? 'comment_forum' : 'comment';
    $row->setSourceProperty('comment_type_name', $comment_type);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields =  parent::fields();
    $fields['comment_type_name'] = $this->t('The comment type.');
    $fields['user_mail'] = $this->t('The email of the user.');
    return $fields;
  }

}
