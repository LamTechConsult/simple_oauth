<?php

/**
 * @file
 * Contains \Drupal\token_auth\Entity\AccessToken.
 */

namespace Drupal\token_auth\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\token_auth\AccessTokenInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Access Token entity.
 *
 * @ingroup token_auth
 *
 * @ContentEntityType(
 *   id = "access_token",
 *   label = @Translation("Access Token"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\token_auth\AccessTokenListBuilder",
 *     "views_data" = "Drupal\token_auth\Entity\AccessTokenViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\token_auth\Entity\Form\AccessTokenForm",
 *       "add" = "Drupal\token_auth\Entity\Form\AccessTokenForm",
 *       "edit" = "Drupal\token_auth\Entity\Form\AccessTokenForm",
 *       "delete" = "Drupal\token_auth\Entity\Form\AccessTokenDeleteForm",
 *     },
 *     "access" = "Drupal\token_auth\AccessTokenAccessControlHandler",
 *   },
 *   base_table = "access_token",
 *   admin_permission = "administer AccessToken entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/access_token/{access_token}",
 *     "edit-form" = "/admin/access_token/{access_token}/edit",
 *     "delete-form" = "/admin/access_token/{access_token}/delete"
 *   },
 *   field_ui_base_route = "access_token.settings"
 * )
 */
class AccessToken extends ContentEntityBase implements AccessTokenInterface {
  use EntityChangedTrait;
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Access Token entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Access Token entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Access Token entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['resource_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Resource'))
      ->setDescription(t('The resource for this Access Token.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'access_token_resource')
      ->setSetting('handler', 'default')
      // TODO: Set a callback to default to the global resource.
      // ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(FALSE)
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['value'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Value'))
      ->setDescription(t('The token value.'))
      ->setSettings(array(
        'max_length' => 128,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['expire'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Expire'))
      ->setSettings(array(
        'unsigned' => TRUE,
        'min' => 0,
      ))
      ->setDefaultValue(120)
      ->setDescription(t('The time while the token is valid after its creation, expressed in seconds.'))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

}