<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginator
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_LIMIT = 20;
    public const MAX_LIMIT = 100;

    /**
     * Paginate a QueryBuilder.
     *
     * @param int $page  Current page number (starting from 1)
     * @param int $limit Number of items per page
     *
     * @return array{items: array, meta: array{currentPage: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function paginate(QueryBuilder $queryBuilder, int $page = self::DEFAULT_PAGE, int $limit = self::DEFAULT_LIMIT): array
    {
        $page = max(1, $page);
        $limit = min(max(1, $limit), self::MAX_LIMIT);

        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new DoctrinePaginator($queryBuilder);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        return [
            'items' => iterator_to_array($paginator),
            'meta' => [
                'currentPage' => $page,
                'itemsPerPage' => $limit,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
            ],
        ];
    }
}
