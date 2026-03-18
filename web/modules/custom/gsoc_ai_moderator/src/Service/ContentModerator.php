<?php

namespace Drupal\gsoc_ai_moderator\Service;

use Drupal\core\config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Service to check text for prohibited content.
 *
 * This centralizes logic so it can be reused by form validation, entity
 * hooks, and any other consumers.  In a real implementation the AI portion
 * would make a request to an external API (OpenAI, Perspective, etc.) but for
 * now the service simply inspects the text for configured keywords.
 */
class ContentModerator {
  /**
   * @var \Drupal\core\config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger, ClientInterface $http_client) {
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
    $this->httpClient = $http_client;
  }

  /**
   * Scan a piece of content and return a structured result.
   *
   * @param string $text
   *   Text to check.
   * @return array
   *   Associative array with keys:
   *   - flagged: bool whether the text triggered any filters.
   *   - keywords: array|string[] of keywords that matched.
   *   - score: float|null toxicity score from AI (0‑1) or NULL when not used.
   */
  public function scanText(string $text): array {
    $config = $this->configFactory->get('gsoc_ai_moderator.settings');
    $keywords = $config->get('prohibited_keywords') ?: [];

    // simple keyword search (case insensitive)
    $found = [];
    foreach ($keywords as $keyword) {
      if ($keyword === '') {
        continue;
      }
      if (stripos($text, $keyword) !== FALSE) {
        $found[] = $keyword;
      }
    }

    $result = [
      'flagged' => !empty($found),
      'keywords' => $found,
      'score' => NULL,
    ];

    // placeholder for AI API call; if use_ai is enabled we could send $text
    // to the configured endpoint and merge the response.  For now we simply
    // note that we would do it.
    if ($config->get('use_ai') && $config->get('ai_endpoint')) {
      // TODO: call external service via $this->httpClient, parse response, and
      // populate 'score' and maybe additional flags.  Example structure:
      //
      // $response = $this->httpClient->post($config->get('ai_endpoint'), [
      //   'json' => ['text' => $text],
      // ]);
      // $data = json_decode($response->getBody(), TRUE);
      // $result['score'] = $data['toxicity_score'] ?? NULL;
      // if (!empty($data['flagged_words'])) {
      //   $result['keywords'] = array_merge($result['keywords'], $data['flagged_words']);
      //   $result['flagged'] = TRUE;
      // }
      
      // For the prototype we just add a dummy score so that future UI can
      // display a "toxicity score" even though nothing happened.
      $result['score'] = 0.0;
    }

    return $result;
  }

  /**
   * Convenience to show a message if something is flagged.  Used by the
   * entity presave hook (programmatic saves) to give feedback without
   * throwing exceptions or blocking the save.
   *
   * @param array $scan_result
   *   The array returned from scanText().
   */
  public function handleFlags(array $scan_result): void {
    if ($scan_result['flagged']) {
      $this->messenger->addWarning(
        t("The content has been flagged for review: @keywords.", [
          '@keywords' => implode(', ', $scan_result['keywords']),
        ])
      );
    }
    else {
      $this->messenger->addStatus(t('Content passed the moderation check.'));
    }
  }
}
