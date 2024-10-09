<?php

namespace Drupal\coderz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'RequestBlock' block.
 *
 * @Block(
 *   id = "coderz_request_block",
 *   admin_label = @Translation("Request Block with Fields and Form")
 * )
 */
class RequestBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new RequestBlock.
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
   *
   */
  public function build() {
    $icon_image_fid = $this->configuration['icon_image'] ?? '';
    $icon_image_url = '';

    if (!empty($icon_image_fid)) {
      // Load the file entity using the file ID.
      $file = File::load($icon_image_fid);
      if ($file) {
        // Generate the URL from the file URI.
        // $icon_image_url = file_create_url($file->getFileUri());
        $icon_image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }
    return [
      '#theme' => 'coderz_request_block',
      '#install_heading' => $this->configuration['install_heading'] ?? '',
      '#best_screens' => $this->configuration['best_screens'] ?? '',
      '#product_description' => $this->configuration['product_description'] ?? '',
      '#phone_number' => $this->configuration['phone_number'] ?? '',
      '#icon_image' => $icon_image_url,
      '#form' => \Drupal::formBuilder()->getForm('Drupal\coderz\Form\ContactForm'),
    ];
  }

  /**
   *
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['install_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Install heading'),
      '#default_value' => $this->configuration['install_heading'] ?? '',
    ];

    $form['best_screens'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Best screens title'),
      '#default_value' => $this->configuration['best_screens'] ?? '',
    ];

    $form['product_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Product Description'),
      '#default_value' => $this->configuration['product_description'] ?? '',
    ];

    $form['phone_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#default_value' => $this->configuration['phone_number'] ?? '',
      '#description' => $this->t('Please enter the phone number with country code.'),
    ];

    $form['icon_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Icon Image (SVG)'),
      '#upload_location' => 'public://icon_images/',
      '#default_value' => !empty($this->configuration['icon_image']) ? [$this->configuration['icon_image']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['svg'],
      ],
    ];

    return $form;
  }

  /**
   *
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['install_heading'] = $form_state->getValue('install_heading');
    $this->configuration['best_screens'] = $form_state->getValue('best_screens');
    $this->configuration['product_description'] = $form_state->getValue('product_description');
    $this->configuration['phone_number'] = $form_state->getValue('phone_number');

    $icon_image = $form_state->getValue('icon_image');
    if (!empty($icon_image) && is_array($icon_image)) {
      $fid = reset($icon_image);
      $this->configuration['icon_image'] = $fid;

      $file = File::load($fid);
      if ($file && $file->isTemporary()) {
        $file->setPermanent();
        $file->save();
      }
    }
    else {
      $this->configuration['icon_image'] = NULL;
    }
  }

}
