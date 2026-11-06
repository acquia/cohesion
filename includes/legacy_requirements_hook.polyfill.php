<?php

namespace Drupal\Core\Hook\Attribute {

  if (!class_exists(LegacyRequirementsHook::class)) {

    /**
     * Polyfills Drupal\Core\Hook\Attribute\LegacyRequirementsHook.
     *
     * This attribute only exists in Drupal core 11.3+ (see
     * https://www.drupal.org/node/3549685). PHP never resolves an attribute's
     * class until something reflects it (e.g.
     * ReflectionAttribute::newInstance()), so #[LegacyRequirementsHook] is
     * harmless on older cores right up until something does that - which is
     * exactly what PHPUnit's bootstrap does while scanning .install files,
     * crashing with "Attribute class ... does not exist" on Drupal 10/11.2.
     * Since attribute references can't be made conditional at the syntax
     * level, this file guarantees the class exists on every core version
     * this module supports, loaded unconditionally via Composer's
     * autoload.files before any module code runs.
     */
    #[\Attribute(\Attribute::TARGET_FUNCTION)]
    class LegacyRequirementsHook {}

  }

}
