<?php

namespace App\Traits\DataTables;

use Yajra\DataTables\DataTables;

trait CommonDataTableTrait
{
    private $default_pagesize = 10;

    protected function applySearchAndOrder($query, $request, $defaultSort = 'created_at')
    {
        // Search
        if ($request->filled('search_term')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search_term . '%')
                  ->orWhere('title', 'LIKE', '%' . $request->search_term . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search_term . '%');
            });
        }

        // Ordering
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy($defaultSort, 'desc');
        }

        return $query;
    }
}
