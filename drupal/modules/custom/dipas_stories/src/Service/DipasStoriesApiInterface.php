<?php

namespace Drupal\dipas_stories\Service;

/**
 * Interface DipasStoriesApiInterface
 *
 * @ingroup dipas_stories
 */
interface DipasStoriesApiInterface {

  /**
   * Determines which answer to send.
   *
   * If no story and no chapter ID is given, the response will be a JSON
   * containing a story overview. If a storys ID is given but no chapter ID is
   * provided, a story structure is returned as JSON. When both story and chapter
   * IDs are provided, the rendered HTML of a story chapter is returned.
   *
   * @param int $storyID
   *   The ID of the story node (optional)
   * @param int $chapterID
   *   The ID of the chapter (optional)
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse|\Drupal\Core\Render\HtmlResponse
   *   The response object for the request
   */
  public function requestResolver($storyID, $chapterID);

  /**
   * Pseudo-field value for the story node edit form.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The edited node.
   *
   * @return int
   *   The accumulated reading time of all associated story steps.
   */
  public function getStoryReadingTime(\Drupal\node\NodeInterface $node);

}
