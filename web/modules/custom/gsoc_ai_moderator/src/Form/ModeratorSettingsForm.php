<?php

namespace Drupal\gsoc_ai_moderator\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ModeratorSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gsoc_ai_moderator_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['gsoc_ai_moderator.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gsoc_ai_moderator.settings');

    $form['prohibited_keywords'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Forbidden keywords'),
      '#description' => $this->t('One word or phrase per line. Content containing any of these strings will be flagged.'),
      '#default_value' => implode("\n", $config->get('prohibited_keywords') ?? []),
    ];

    $form['use_ai'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable AI scanning'),
      '#default_value' => $config->get('use_ai'),
    ];

    $form['ai_endpoint'] = [
      '#type' => 'url',
      '#title' => $this->t('AI API endpoint'),
      '#description' => $this->t('If you enable AI scanning, specify the URL to call. The prototype does not actually contact it.'),
      '#default_value' => $config->get('ai_endpoint'),
      '#states' => [
        'visible' => [
          ':input[name="use_ai"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $keywords = preg_split('/\r?\n/', $values['prohibited_keywords'], -1, PREG_SPLIT_NO_EMPTY);
    $this->config('gsoc_ai_moderator.settings')
      ->set('prohibited_keywords', $keywords)
      ->set('use_ai', $values['use_ai'])
      ->set('ai_endpoint', $values['ai_endpoint'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
