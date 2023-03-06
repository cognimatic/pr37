<?php

declare(strict_types=1);

namespace Drupal\auditfiles\Reference;

/**
 * Represents an entry on disk.
 */
final class DiskReference implements ReferenceInterface {

  /**
   * Constructs a new DiskReference.
   */
  private function __construct(
    private string $uri,
  ) {
  }

  public static function create(string $path): static {
    return new static($path);
  }

  /**
   * @return string
   */
  public function getUri(): string {
    return $this->uri;
  }

  public function __toString(): string {
    return sprintf('File on disk at: %s', $this->uri);
  }


}
