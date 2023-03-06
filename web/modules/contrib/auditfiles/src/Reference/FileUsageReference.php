<?php

declare(strict_types=1);

namespace Drupal\auditfiles\Reference;

/**
 * Represents an entry in the file usage table.
 */
final class FileUsageReference implements ReferenceInterface {

  /**
   * Constructs a new FileUsageReference.
   */
  private function __construct(
    private int $fileId,
    private string $module,
    private string $entityTypeId,
    private string|int $entityId,
    private int $count,
  ) {
  }

  public static function create(int $fileId, string $module, string $entityTypeId, string|int $entityId, int $count): static {
    return new static($fileId, $module, $entityTypeId, $entityId, $count);
  }

  /**
   * @param \stdClass{fid: string, module: string, type: string, id: string|int, count: string}
   */
  public static function createFromRow(\stdClass $row): static {
    return static::create((int) $row->fid, $row->module, $row->type, $row->id, (int) $row->count);
  }

  public function __toString(): string {
    return sprintf('File usage');
  }

  /**
   * @return int
   */
  public function getFileId(): int {
    return $this->fileId;
  }

  /**
   * @return string
   */
  public function getModule(): string {
    return $this->module;
  }

  /**
   * @return string
   */
  public function getEntityTypeId(): string {
    return $this->entityTypeId;
  }

  /**
   * @return int|string
   */
  public function getEntityId(): int|string {
    return $this->entityId;
  }

  /**
   * @return int
   */
  public function getCount(): int {
    return $this->count;
  }

}
