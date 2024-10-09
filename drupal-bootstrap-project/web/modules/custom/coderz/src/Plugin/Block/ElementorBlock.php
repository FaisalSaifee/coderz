<?php

namespace Drupal\coderz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Elementor Block' block.
 *
 * @Block(
 *   id = "coderz_elementor_block",
 *   admin_label = @Translation("Elementor Block with Images, Titles, and Descriptions")
 * )
 */
class ElementorBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new ElementorBlock.
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
    // Prepare the items array to render.
    $items = [];

    for ($i = 1; $i <= 4; $i++) {
      // Get configuration for each item.
      $image_fid = $this->configuration["image_$i"] ?? '';
      $image_url = '';

      if (!empty($image_fid)) {
        $file = File::load($image_fid);
        if ($file) {
          $image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }

      $items[] = [
        'title' => $this->configuration["title_$i"] ?? '',
        'description' => $this->configuration["description_$i"] ?? '',
        'image' => $image_url,
      ];
    }

    return [
      '#theme' => 'coderz_elementor_block',
      '#items' => $items,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    for ($i = 1; $i <= 4; $i++) {
      $form["title_$i"] = [
        '#type' => 'textfield',
        '#title' => $this->t("Title $i"),
        '#default_value' => $this->configuration["title_$i"] ?? '',
      ];

      $form["description_$i"] = [
        '#type' => 'textarea',
        '#title' => $this->t("Description $i"),
        '#default_value' => $this->configuration["description_$i"] ?? '',
      ];

      $form["image_$i"] = [
        '#type' => 'managed_file',
        '#title' => $this->t("Image $i"),
        '#upload_location' => 'public://images/',
        '#default_value' => isset($this->configuration["image_$i"]) ? [$this->configuration["image_$i"]] : '',
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg gif svg'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    for ($i = 1; $i <= 4; $i++) {
      $this->configuration["title_$i"] = $form_state->getValue("title_$i");
      $this->configuration["description_$i"] = $form_state->getValue("description_$i");

      $image = $form_state->getValue("image_$i");
      if (!empty($image) && is_array($image)) {
        $fid = reset($image);
        $this->configuration["image_$i"] = $fid;

        $file = File::load($fid);
        if ($file && $file->isTemporary()) {
          $file->setPermanent();
          $file->save();
        }
      }
    }
  }

}
