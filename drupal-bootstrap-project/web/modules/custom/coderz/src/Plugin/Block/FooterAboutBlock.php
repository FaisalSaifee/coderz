<?php

namespace Drupal\coderz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Footer About' block.
 *
 * @Block(
 *   id = "coderz_footer_about_block",
 *   admin_label = @Translation("Footer About Block")
 * )
 */
class FooterAboutBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new FooterAboutBlock.
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
    $icons = [];

    for ($i = 1; $i <= 4; $i++) {
      $icon_url = $this->getImageUrl("social_icon_$i");
      $link = $this->configuration["social_link_$i"] ?? '';

      $icons[] = [
        'icon_url' => $icon_url,
        'link' => $link,
      ];
    }

    return [
      '#theme' => 'coderz_footer_about_block',
      '#description' => $this->configuration['description'] ?? '',
      '#icons' => $icons,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $this->configuration['description'] ?? '',
    ];

    for ($i = 1; $i <= 4; $i++) {
      $form["social_icon_$i"] = [
        '#type' => 'managed_file',
        '#title' => $this->t("Social Icon $i"),
        '#upload_location' => 'public://icons/',
        '#default_value' => !empty($this->configuration["social_icon_$i"]) ? [$this->configuration["social_icon_$i"]] : '',
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg svg'],
        ],
      ];

      $form["social_link_$i"] = [
        '#type' => 'textfield',
        '#title' => $this->t("Social Link $i"),
        '#default_value' => $this->configuration["social_link_$i"] ?? '',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['description'] = $form_state->getValue('description');

    for ($i = 1; $i <= 4; $i++) {
      $this->configuration["social_icon_$i"] = $this->saveImage($form_state->getValue("social_icon_$i"));
      $this->configuration["social_link_$i"] = $form_state->getValue("social_link_$i");
    }
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
