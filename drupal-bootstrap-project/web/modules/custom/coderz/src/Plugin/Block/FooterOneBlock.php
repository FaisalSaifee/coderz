<?php

namespace Drupal\coderz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Footer One' block.
 *
 * @Block(
 *   id = "coderz_footer_one_block",
 *   admin_label = @Translation("Footer One Block")
 * )
 */
class FooterOneBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new FooterOneBlock.
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
    $cards = [];

    for ($i = 1; $i <= 3; $i++) {
      $image_fid = $this->configuration["card_image_$i"] ?? '';
      $image_url = '';

      if (!empty($image_fid)) {
        $file = File::load($image_fid);
        if ($file) {
          $image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }

      $cards[] = [
        'image' => $image_url,
        'text' => [
          '#type' => 'processed_text',
          '#text' => $this->configuration["card_text_$i"]['value'] ?? '',
          '#format' => $this->configuration["card_text_$i"]['format'] ?? 'basic_html',
        ],
      ];
    }

    return [
      '#theme' => 'coderz_footer_one_block',
      '#cards' => $cards,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    for ($i = 1; $i <= 3; $i++) {
      $form["card_text_$i"] = [
        '#type' => 'text_format',
        '#title' => $this->t("Card $i Text"),
        '#format' => $this->configuration["card_text_{$i}_format"] ?? 'basic_html',
        '#default_value' => $this->configuration["card_text_$i"]['value'] ?? '',
      ];

      $form["card_image_$i"] = [
        '#type' => 'managed_file',
        '#title' => $this->t("Card $i Image"),
        '#upload_location' => 'public://images/',
        '#default_value' => !empty($this->configuration["card_image_$i"]) ? [$this->configuration["card_image_$i"]] : '',
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
    for ($i = 1; $i <= 3; $i++) {
      $text_value = $form_state->getValue("card_text_$i");

      $this->configuration["card_text_$i"] = [
        'value' => $text_value['value'],
        'format' => $text_value['format'],
      ];

      $image = $form_state->getValue("card_image_$i");
      if (!empty($image) && is_array($image)) {
        $fid = reset($image);
        $this->configuration["card_image_$i"] = $fid;

        $file = File::load($fid);
        if ($file && $file->isTemporary()) {
          $file->setPermanent();
          $file->save();
        }
      }
    }
  }

}
