<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chunked upload controller.
 *
 * @package Drupal\cohesion_sync\Controller
 */
class ChunkedUploadController extends ControllerBase {

  /**
   * Stream the chunks to a temporary file and return the URI.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function receiveChunkedUpload(Request $request) {
    $user = \Drupal::currentUser();

    $tmp_stream_wrapper = Settings::get('coh_temporary_stream_wrapper', 'temporary://');

    // Get a unique filename for this upload.
    $temp_uri = \Drupal::service('file_system')->createFilename(basename($request->headers->get('filename')), $tmp_stream_wrapper);

    // Read contents from the input stream.
    $inputHandler = fopen('php://input', "r");

    // Create a temp file where to save data from the input stream.
    if ($fileHandler = fopen($temp_uri, "w+")) {

      // Save data from the input stream.
      while (TRUE) {
        $buffer = fgets($inputHandler, 4096);
        if (strlen($buffer) == 0) {
          fclose($inputHandler);
          fclose($fileHandler);

          // Create a temporary managed file from this URI.
          $file = File::create([
            'uri' => $temp_uri,
            'uid' => $user->id(),
            'status' => 0,
          ]);
          $file->save();

          // Send success.
          $response = new Response();
          $response->setStatusCode(200);
          $response->setContent($file->id());
          return $response;
        }

        fwrite($fileHandler, $buffer);
      }
    }
    else {
      $response = new Response();
      $response->setStatusCode(400);
      $response->setContent('Failed to create temporary file in temporary://');
      return $response;
    }
  }

}
