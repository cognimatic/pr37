<?php

namespace Drupal\content_readability\Controller;

use Drupal\Core\Controller\ControllerBase;
use DaveChild\TextStatistics\TextStatistics;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns responses for content readability  ajax routes.
 */
class ContentReadabilityAjaxController extends ControllerBase {

  /**
   * Drupal\Core\Render\RendererInterface definition.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * ContentReadabilityAjaxController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Render Interface.
   */
  public function __construct(RequestStack $request, RendererInterface $renderer) {
    $this->request = $request;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('renderer')

    );
  }

  /**
   * Update content readability score.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response with new scores.
   */
  public function updateScore() {
    $body = $this->request->getCurrentRequest()->request->get('data');
    $grade = $this->request->getCurrentRequest()->request->get('grade');
    $failures = FALSE;

    $textStatistics = new TextStatistics();
    $fleschKincaidGradeLevel = $textStatistics->fleschKincaidGradeLevel($body);
    $contentReadabilityScore = content_readability_get_content_readability_score($fleschKincaidGradeLevel, $grade);
    $averageSyllablesPerWord = round($textStatistics->averageSyllablesPerWord($body), 2);
    $averageWordsPerSentence = round($textStatistics->averageWordsPerSentence($body), 2);
    $colemanLiauIndex = $textStatistics->colemanLiauIndex($body);
    $smogIndex = $textStatistics->smogIndex($body);
    $automatedReadabilityIndex = $textStatistics->automatedReadabilityIndex($body);
    $gunningFogScore = $textStatistics->gunningFogScore($body);
    $daleChallReadabilityScore = $textStatistics->daleChallReadabilityScore($body);
    $spacheReadabilityScore = $textStatistics->spacheReadabilityScore($body);
    $fleschKincaidReadingEase = $textStatistics->fleschKincaidReadingEase($body);
    $daleChallDifficultWordCount = $textStatistics->daleChallDifficultWordCount($body);
    $spacheDifficultWordCount = $textStatistics->spacheDifficultWordCount($body);
    $letterCount = $textStatistics->letterCount($body);
    $wordCount = $textStatistics->wordCount($body);
    $totalSyllables = $textStatistics->totalSyllables($body);
    $sentenceCount = $textStatistics->sentenceCount($body);
    $wordsWithThreeSyllables = $textStatistics->wordsWithThreeSyllables($body);
    $percentageWordsWithThreeSyllables = round($textStatistics->percentageWordsWithThreeSyllables($body), 2);

    $updatedValues = [
      "contentReadabilityScore" => $contentReadabilityScore,
      "fleschKincaidGradeLevel" => $fleschKincaidGradeLevel,
      "averageWordsPerSentence" => $averageWordsPerSentence,
      "averageSyllablesPerWord" => $averageSyllablesPerWord,
      "colemanLiauIndex" => $colemanLiauIndex,
      "smogIndex" => $smogIndex,
      "automatedReadabilityIndex" => $automatedReadabilityIndex,
      "gunningFogScore" => $gunningFogScore,
      "daleChallReadabilityScore" => $daleChallReadabilityScore,
      "spacheReadabilityScore" => $spacheReadabilityScore,
      "fleschKincaidReadingEase" => $fleschKincaidReadingEase,
      "daleChallDifficultWordCount" => $daleChallDifficultWordCount,
      "spacheDifficultWordCount" => $spacheDifficultWordCount,
      "letterCount" => $letterCount,
      "wordCount" => $wordCount,
      "totalSyllables" => $totalSyllables,
      "sentenceCount" => $sentenceCount,
      "wordsWithThreeSyllables" => $wordsWithThreeSyllables,
      "percentageWordsWithThreeSyllables" => $percentageWordsWithThreeSyllables,
    ];

    // @todo Add some type of error handling.
    if ($failures) {
      return new JsonResponse('Error(s) occurred while updating user items. Please check the logs.');
    }
    else {
      return new JsonResponse($updatedValues);
    }
  }

}
