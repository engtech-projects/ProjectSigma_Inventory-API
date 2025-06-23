<?php

namespace App\Utils;

use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class PaginateResourceCollection
{
    public static function paginate(Collection $results, $showPerPage =  10)
    {

        $pageNumber = Paginator::resolveCurrentPage('page');
        $totalPageNumber = $results->count();
        $offset = ($pageNumber * $showPerPage) - $showPerPage;
        $items = collect(array_slice($results->toArray(), $offset, $showPerPage));
        return self::paginator($items, $totalPageNumber, $showPerPage, $pageNumber, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    protected static function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items',
            'total',
            'perPage',
            'currentPage',
            'options'
        ));
    }
}
