<?php

namespace Drupal\coderz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Footer Contact' block.
 *
 * @Block(
 *   id = "coderz_footer_contact_block",
 *   admin_label = @Translation("Footer Contact Block")
 * )
 */
class FooterContactBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new FooterContactBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $logo_url = $this->getImageUrl('company_logo');
    $location_icon_url = $this->getImageUrl('location_icon');
    $email_icon_url = $this->getImageUrl('email_icon');
    $phone_icon_url = $this->getImageUrl('phone_icon');

    return [
      '#theme' => 'coderz_footer_contact_block',
      '#company_logo' => $logo_url,
      '#location_icon' => $location_icon_url,
      '#address' => $this->configuration['address'] ?? '',
      '#email_icon' => $email_icon_url,
      '#email_address' => $this->configuration['email_address'] ?? '',
      '#phone_icon' => $phone_icon_url,
      '#phone_number' => $this->configuration['phone_number'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['company_logo'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Company Logo'),
      '#upload_location' => 'public://images/',
      '#default_value' => !empty($this->configuration['company_logo']) ? [$this->configuration['company_logo']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg svg'],
      ],
    ];

    $form['location_icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Location Icon'),
      '#upload_location' => 'public://icons/',
      '#default_value' => !empty($this->configuration['location_icon']) ? [$this->configuration['location_icon']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg svg'],
      ],
    ];

    $form['address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Company Address'),
      '#default_value' => $this->configuration['address'] ?? '',
    ];

    $form['email_icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Email Icon'),
      '#upload_location' => 'public://icons/',
      '#default_value' => !empty($this->configuration['email_icon']) ? [$this->configuration['email_icon']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg svg'],
      ],
    ];

    $form['email_address'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#default_value' => $this->configuration['email_address'] ?? '',
    ];

    $form['phone_icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Phone Icon'),
      '#upload_location' => 'public://icons/',
      '#default_value' => !empty($this->configuration['phone_icon']) ? [$this->configuration['phone_icon']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg svg'],
      ],
    ];

    $form['phone_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone Number'),
      '#default_value' => $this->configuration['phone_number'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['company_logo'] = $this->saveImage($form_state->getValue('company_logo'));
    $this->configuration['location_icon'] = $this->saveImage($form_state->getValue('location_icon'));
    $this->configuration['email_icon'] = $this->saveImage($form_state->getValue('email_icon'));
    $this->configuration['phone_icon'] = $this->saveImage($form_state->getValue('phone_icon'));

    $this->configuration['address'] = $form_state->getValue('address');
    $this->configuration['email_address'] = $form_state->getValue('email_address');
    $this->configuration['phone_number'] = $form_state->getValue('phone_number');
  }

  /**
   * Helper function to get image URL.
   */
  private function getImageUrl($field_name) {
    $image_fid = $this->configuration[$field_name] ?? '';
    if ($image_fid) {
      $file = File::load($image_fid);
      if ($file) {
        return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }
    return '';
  }

  /**
   * Helper function to save an image and make it permanent.
   */
  private function saveImage($image) {
    if (!empty($image) && is_array($image)) {
      $fid = reset($image);
      $file = File::load($fid);
      if ($file && $file->isTemporary()) {
        $file->setPermanent();
        $file->save();
      }
      return $fid;
    }
    return NULL;
  }

}
