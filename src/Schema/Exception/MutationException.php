<?php


namespace SilverStripe\GraphQL\Schema\Exception;

use Exception;
use GraphQL\Error\ClientAware;

/**
 * Thrown when a mutation operation fails
 */
class MutationException extends Exception implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }
}
