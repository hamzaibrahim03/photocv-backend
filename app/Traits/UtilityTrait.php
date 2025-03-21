<?php

namespace App\Traits;

use Yajra\DataTables\DataTables;
use App\Models\Event;
use App\Models\Competition;

trait UtilityTrait
{
    private $default_pagesize = 10;

    public function getAlltraitdata($request, $query)
    {
        // Apply global search filter
        if ($request->has('search_term') && $request->search_term != '') {
           $query =  $this->applySearchFilter($query, $request->search_term);
        }
        // Apply sorting (DataTables automatically sends the column and direction)
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        $request->start = $request->start ?? 0;
        $request->length = $request->length ? $request->length : $this->default_pagesize;

        // Pagination (use skip for offset and take for limit)
        $data = $query->skip((int) $request->start)
                      ->take((int) $request->length)
                      ->get();

        return DataTables::of($data)->addIndexColumn()->make(true);
    }

    public function getAllEventData($request)
    {
        $query = Event::with('images');

        // Apply search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where('name', 'LIKE', '%' . $request->search_term . '%')
                  ->orWhere('speaker', 'LIKE', '%' . $request->search_term . '%');
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($event) {
                return '<a href="'.route('events.show', $event->id).'" class="btn btn-sm btn-primary">View</a>';
            })
            ->make(true);
    }

    public function getAllIndexData($request, $query)
    {
        // Apply search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where('name', 'LIKE', '%' . $request->search_term . '%')
                  ->orWhere('speaker', 'LIKE', '%' . $request->search_term . '%');
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($event) {
                return '<a href="'.route('events.show', $event->id).'" class="btn btn-sm btn-primary">View</a>';
            })
            ->make(true);
    }

}
