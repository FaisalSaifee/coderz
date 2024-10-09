<?php

namespace Drupal\coderz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'BannerBlock' block.
 *
 * @Block(
 *   id = "banner_block",
 *   admin_label = @Translation("Banner Block"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class BannerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new BannerBlock.
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $file_url_generator) {
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
    // Load the image file if available.
    $config = $this->getConfiguration();
    $image_fid = $config['banner_image'] ?? '';
    $image_url = '';

    if (!empty($image_fid)) {
      $file = File::load($image_fid);
      if ($file) {
        $image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }

    return [
      '#theme' => 'coderz_banner_block',
      '#image_url' => $image_url,
      '#banner_heading' => $config['banner_heading'] ?? '',
      '#banner_subheading' => $config['banner_subheading'] ?? '',
      '#banner_description' => $config['banner_description'] ?? '',
      '#button1_text' => $config['button1_text'] ?? $this->t('Book a Schedule'),
      '#button1_link' => $config['button1_link'] ?? '#',
      '#button2_text' => $config['button2_text'] ?? $this->t('About Us'),
      '#button2_link' => $config['button2_link'] ?? '#',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    // Image field.
    $form['banner_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Banner Image'),
      '#upload_location' => 'public://banner_images/',
      '#default_value' => isset($config['banner_image']) ? [$config['banner_image']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
      ],
      '#description' => $this->t('Upload an image for the banner.'),
    ];

    // Heading, subheading, and description fields.
    $form['banner_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Banner Heading'),
      '#default_value' => $config['banner_heading'] ?? '',
    ];

    $form['banner_subheading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Banner Subheading'),
      '#default_value' => $config['banner_subheading'] ?? '',
    ];

    $form['banner_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Banner Description'),
      '#default_value' => $config['banner_description'] ?? '',
    ];

    // Button 1 (text and link)
    $form['button1_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button 1 Text'),
      '#default_value' => $config['button1_text'] ?? $this->t('Book a Schedule'),
    ];

    $form['button1_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button 1 Link'),
      '#default_value' => $config['button1_link'] ?? '#',
      '#description' => $this->t('Enter a relative or absolute URL (e.g., /page or https://example.com)'),
    ];

    // Button 2 (text and link)
    $form['button2_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button 2 Text'),
      '#default_value' => $config['button2_text'] ?? $this->t('About Us'),
    ];

    $form['button2_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button 2 Link'),
      '#default_value' => $config['button2_link'] ?? '#',
      '#description' => $this->t('Enter a relative or absolute URL (e.g., /page or https://example.com)'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('banner_heading', $form_state->getValue('banner_heading'));
    $this->setConfigurationValue('banner_subheading', $form_state->getValue('banner_subheading'));
    $this->setConfigurationValue('banner_description', $form_state->getValue('banner_description'));
    $this->setConfigurationValue('button1_text', $form_state->getValue('button1_text'));
    $this->setConfigurationValue('button1_link', $form_state->getValue('button1_link'));
    $this->setConfigurationValue('button2_text', $form_state->getValue('button2_text'));
    $this->setConfigurationValue('button2_link', $form_state->getValue('button2_link'));

    $banner_image = $form_state->getValue('banner_image');
    if (!empty($banner_image) && is_array($banner_image)) {
      $fid = reset($banner_image);
      $this->setConfigurationValue('banner_image', $fid);

      $file = File::load($fid);
      if ($file && $file->isTemporary()) {
        $file->setPermanent();
        $file->save();
      }
    }
  }

}
