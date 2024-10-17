<?php

namespace Drupal\cohesion\Controller;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class MachineNameController.
 *
 * This extends the core machine name controller to generate unique.
 *
 * @package Drupal\cohesion\Controller
 */
class MachineNameController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The entity storage interface.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * MachineNameController constructor.
   *
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   * @param \Drupal\Core\Access\CsrfTokenGenerator $tokenGenerator
   */
  public function __construct(
    protected readonly TransliterationInterface $transliteration,
    protected readonly CsrfTokenGenerator $tokenGenerator,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('transliteration'),
      $container->get('csrf_token'),
    );
  }

  /**
   * Transliterates a string in the given language.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The input string and language for the transliteration.
   *   Optionally contain the replace_pattern, replace, lowercase parameters.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The transliterated string.
   */
  public function transliterate(Request $request) {
    $text = $request->query->get('text');
    $langcode = $request->query->get('langcode');
    $replace_pattern = $request->query->get('replace_pattern');
    $replace_token = $request->query->get('replace_token');
    $replace = $request->query->get('replace');
    $lowercase = $request->query->get('lowercase');
    $entity_type_id = $request->query->get('entity_type_id');
    $entity_id = $request->query->get('entity_id');
    $field_prefix = $request->query->get('field_prefix');
    $maxlength = $request->query->get('maxlength');

    // Attempt to get the storage for this entity type.
    try {
      $this->storage = $this->entityTypeManager()->getStorage($entity_type_id);
    }
    catch (\Throwable $e) {
      return new JsonResponse('');
    }

    // Transliterate.
    $transliterated = $this->transliteration->transliterate($text, $langcode, '_');
    if ($lowercase) {
      $transliterated = mb_strtolower($transliterated);
    }

    if (isset($replace_pattern) && isset($replace)) {
      if (!isset($replace_token)) {
        throw new AccessDeniedHttpException("Missing 'replace_token' query parameter.");
      }
      elseif (!$this->tokenGenerator->validate($replace_token, $replace_pattern)) {
        throw new AccessDeniedHttpException("Invalid 'replace_token' query parameter.");
      }

      // Quote the pattern delimiter and remove null characters to avoid the
      // or other modifiers being injected.
      $transliterated = preg_replace('@' . strtr($replace_pattern, ['@' => '\@', chr(0) => '']) . '@', $replace, $transliterated);
      // phpcs:enable
    }

    // Get a unique transliterated string.
    $transliterated = $this->getUniqueEntityId($transliterated, $field_prefix, $entity_id, $maxlength);

    return new JsonResponse($transliterated);
  }

  /**
   * Given machine name input, get a unique machine name to avoid
   * duplicate errors.
   *
   * @param $input
   * @param $field_prefix
   * @param $entity_id
   * @param $maxlength
   *
   * @return string
   */
  public function getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength) {
    $index = -1;

    do {
      if ($index == -1) {
        // Usually the first pass.
        $id_string = substr($input, 0, $maxlength);
      }
      else {
        $id_string = substr($input, 0, $maxlength - strlen($index) - 1) . '_' . $index;
      }

      $index += 1;
    } while ($this->storage->load($field_prefix . $id_string) && ($field_prefix . $id_string) !== $entity_id);

    return $id_string;
  }

}
