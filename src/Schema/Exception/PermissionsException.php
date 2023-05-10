<?php


namespace SilverStripe\GraphQL\Schema\Exception;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

/**
 * Thrown when an operation encounters a permissions problem, e.g. lack of read/write
 * permissions
 */
class PermissionsException extends Exception implements ClientAware, ProvidesExtensions
{
    /**
     * @inheritDoc
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getExtensions(): ?array
    {
        return ['category' => 'permission'];
    }
}
