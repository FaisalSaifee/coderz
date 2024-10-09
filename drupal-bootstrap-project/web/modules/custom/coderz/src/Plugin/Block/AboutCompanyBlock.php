<?php

namespace Drupal\coderz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'About Our Company' block.
 *
 * @Block(
 *   id = "coderz_about_company_block",
 *   admin_label = @Translation("About Our Company Block")
 * )
 */
class AboutCompanyBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new AboutCompanyBlock.
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
    $normal_image_url = '';
    $video_image_url = '';

    if (!empty($this->configuration['normal_image'])) {
      $file = File::load($this->configuration['normal_image']);
      if ($file) {
        $normal_image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }

    if (!empty($this->configuration['video_image'])) {
      $file = File::load($this->configuration['video_image']);
      if ($file) {
        $video_image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }

    return [
      '#theme' => 'coderz_about_company_block',
      '#title' => $this->configuration['title'] ?? '',
      '#our_factory' => $this->configuration['our_factory'] ?? '',
      '#description' => $this->configuration['description'] ?? '',
      '#button_text' => $this->configuration['button_text'] ?? '',
      '#button_link' => $this->configuration['button_link'] ?? '',
      '#normal_image' => $normal_image_url,
      '#video_image' => $video_image_url,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->configuration['title'] ?? '',
    ];

    $form['our_factory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Our Factory'),
      '#default_value' => $this->configuration['our_factory'] ?? '',
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $this->configuration['description'] ?? '',
    ];

    $form['button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Text'),
      '#default_value' => $this->configuration['button_text'] ?? '',
    ];

    $form['button_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Link'),
      '#default_value' => $this->configuration['button_link'] ?? '',
    ];

    $form['normal_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Normal Image'),
      '#upload_location' => 'public://images/',
      '#default_value' => !empty($this->configuration['normal_image']) ? [$this->configuration['normal_image']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg gif svg'],
      ],
    ];

    $form['video_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Video Image'),
      '#upload_location' => 'public://images/',
      '#default_value' => !empty($this->configuration['video_image']) ? [$this->configuration['video_image']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg gif svg'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['our_factory'] = $form_state->getValue('our_factory');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['button_text'] = $form_state->getValue('button_text');
    $this->configuration['button_link'] = $form_state->getValue('button_link');

    $normal_image = $form_state->getValue('normal_image');
    if (!empty($normal_image) && is_array($normal_image)) {
      $fid = reset($normal_image);
      $this->configuration['normal_image'] = $fid;
      $file = File::load($fid);
      if ($file && $file->isTemporary()) {
        $file->setPermanent();
        $file->save();
      }
    }

    $video_image = $form_state->getValue('video_image');
    if (!empty($video_image) && is_array($video_image)) {
      $fid = reset($video_image);
      $this->configuration['video_image'] = $fid;
      $file = File::load($fid);
      if ($file && $file->isTemporary()) {
        $file->setPermanent();
        $file->save();
      }
    }
  }

}
