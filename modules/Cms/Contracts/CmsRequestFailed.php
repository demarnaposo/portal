<?php

namespace Modules\Cms\Contracts;

use RuntimeException;

/** `status` 0 = tidak terhubung/timeout; selain itu status HTTP dari CMS dengan body JSON-nya. */
final class CmsRequestFailed extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 0, public readonly array $body = [])
    {
        parent::__construct($message);
    }
}
