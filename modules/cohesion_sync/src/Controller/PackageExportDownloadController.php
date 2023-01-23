<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles Package Export Downloads.
 *
 * @package Drupal\cohesion_sync\Controller
 */
class PackageExportDownloadController extends ControllerBase {

  /**
   * Handles fle download from temporary storage.
   *
   * @param string $filename
   *   Filename to attempt a download on.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   Binary File Response.
   */
  public function download(string $filename) {
    $uri = 'temporary://' . $filename;
    if (is_file($uri)) {
      // Let other modules provide headers and controls access to the file.
      $headers = $this->moduleHandler()->invokeAll('file_download', [$uri]);

      foreach ($headers as $result) {
        if ($result == -1) {
          throw new AccessDeniedHttpException();
        }
      }

      $response = new BinaryFileResponse($uri, 200, $headers);
      $response->setContentDisposition('inline', $filename);

      return $response;
    }
    throw new NotFoundHttpException();
  }

}
