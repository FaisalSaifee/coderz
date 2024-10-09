<?php

namespace Drupal\coderz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Mosquito Net' block.
 *
 * @Block(
 *   id = "coderz_mosquito_net_block",
 *   admin_label = @Translation("Mosquito Net Block")
 * )
 */
class MosquitoNetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new MosquitoNetBlock.
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
    $big_image_url = '';

    // Get big image for the right part.
    if (!empty($this->configuration['big_image'])) {
      $file = File::load($this->configuration['big_image']);
      if ($file) {
        $big_image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }

    // Prepare the 8 small cards.
    for ($i = 1; $i <= 8; $i++) {
      $card_image_fid = $this->configuration["card_image_$i"] ?? '';
      $card_image_url = '';

      if (!empty($card_image_fid)) {
        $file = File::load($card_image_fid);
        if ($file) {
          $card_image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }

      $cards[] = [
        'title' => $this->configuration["card_title_$i"] ?? '',
        'image' => $card_image_url,
      ];
    }

    return [
      '#theme' => 'coderz_mosquito_net_block',
      '#title' => $this->configuration['title'] ?? '',
      '#big_image' => $big_image_url,
      '#cards' => $cards,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Add the title field.
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Block Title'),
      '#default_value' => $this->configuration['title'] ?? '',
    ];

    // Add the field for the big image.
    $form['big_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Big Image (Right Side)'),
      '#upload_location' => 'public://images/',
      '#default_value' => !empty($this->configuration['big_image']) ? [$this->configuration['big_image']] : '',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg gif svg'],
      ],
    ];

    // Add fields for the 8 small cards (image and title)
    for ($i = 1; $i <= 8; $i++) {
      $form["card_title_$i"] = [
        '#type' => 'textfield',
        '#title' => $this->t("Card $i Title"),
        '#default_value' => $this->configuration["card_title_$i"] ?? '',
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
    $this->configuration['title'] = $form_state->getValue('title');

    $big_image = $form_state->getValue('big_image');
    if (!empty($big_image) && is_array($big_image)) {
      $fid = reset($big_image);
      $this->configuration['big_image'] = $fid;

      $file = File::load($fid);
      if ($file && $file->isTemporary()) {
        $file->setPermanent();
        $file->save();
      }
    }

    for ($i = 1; $i <= 8; $i++) {
      $this->configuration["card_title_$i"] = $form_state->getValue("card_title_$i");

      $card_image = $form_state->getValue("card_image_$i");
      if (!empty($card_image) && is_array($card_image)) {
        $fid = reset($card_image);
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
