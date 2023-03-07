<?php

namespace Drupal\cohesion\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is fired when pre a Site Studio rebuild.
 */
class PostRebuildEvent extends Event {

  /**
   * Results array from the rebuild callback containing any errors.
   *
   * @var array
   */
  protected $results;


  /**
   * If the rebuild is a success with no fatal PHP errors were detected.
   * Other errors handled in results.
   *
   * @var bool
   */
  protected $success;

  /**
   * PostRebuildEvent constructor.
   *
   * @param $results
   * @param $success
   *
   */
  public function __construct($results, $success) {
    $this->results = $results;
    $this->success = $success;
  }

  /**
   * @return array
   */
  public function getResults() {
    return $this->results;
  }

  /**
   * @return bool
   */
  public function getSuccess() {
    return $this->success;
  }

  /**
   * @return bool
   */
  public function rebuildSuccess() {
    if ($this->success && !isset($this->results['error'])) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

}
