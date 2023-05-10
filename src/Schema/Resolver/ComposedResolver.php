<?php


namespace SilverStripe\GraphQL\Schema\Resolver;

use Closure;
use Exception;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injectable;

/**
 * Given a stack of resolver middleware and afterware, compress it into one composed function,
 * passing along the return value.
 */
class ComposedResolver
{
    use Injectable;

    /**
     * @var callable[]
     */
    private array $resolvers;

    /**
     * @param callable[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function toClosure(): Closure
    {
        return function (...$params) {
            $isDone = false;
            $done = function () use (&$isDone) {
                $isDone = true;
            };
            $params[] = $done;
            $obj = array_shift($params);
            $callables = $this->resolvers;
            $result = $obj;
            $index = 0;
            foreach ($callables as $callable) {
                if ($isDone) {
                    return $result;
                }
                $args = array_merge([$result], $params);
                try {
                    $result = call_user_func_array($callable, $args);
                } catch (Exception $e) {
                    throw new Error(
                        $e->getMessage(),
                        previous: $e,
                        extensions: Director::isDev()
                            ? [
                                'resolverIndex' => $index,
                                'executionChain' => $this->executionChain(
                                    $args[3],
                                ),
                            ]
                            : null,
                    );
                }
                $index++;
            }

            return $result;
        };
    }

    /**
     * @param ResolveInfo|null $info
     * @return string
     */
    private function executionChain(?ResolveInfo $info): string
    {
        if (!$info) {
            return '(unknown)';
        }
        $allCallables =
            $info->fieldDefinition->config['resolverComposition'] ?? [];
        $callables = array_map(function ($callable) {
            try {
                return @var_export($callable, true);
            } catch (\Throwable $e) {
                return '(unknown)';
            }
        }, $allCallables ?? []);
        return implode("\n", $callables);
    }
}
