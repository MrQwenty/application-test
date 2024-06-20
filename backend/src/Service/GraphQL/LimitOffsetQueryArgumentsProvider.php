<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\QueryHelper\QueryCriteria;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use function array_key_exists;

final class LimitOffsetQueryArgumentsProvider
{

    public function __construct(private QueryArgumentsProvider $queryArgumentsProvider)
    {
    }

    /**
     * Builds a QueryCriteria object based on raw query data
     */
    public function toQueryCriteria(ArgumentInterface $args, ?ResolveInfo $info = null): QueryCriteria
    {
        $queryCriteria = new QueryCriteria();
        $queryCriteria
            ->setFiltersDescriptor($this->queryArgumentsProvider->toFiltersDescriptor($args))
            ->setOffsetAndLimitDescriptor($this->queryArgumentsProvider->toOffsetAndLimitDescriptor($args))
            ->setSortingDescriptor($this->queryArgumentsProvider->toSortingDescriptor($args))
            ->setTotalCount(isset($info) && !array_key_exists('totalCount', $info->getFieldSelection()) ? false : true)
            ->readOnly();

        return $queryCriteria;
    }
}
