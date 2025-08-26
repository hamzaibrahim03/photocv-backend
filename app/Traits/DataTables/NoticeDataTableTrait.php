<?php

namespace App\Traits\DataTables;

use App\Models\MemberNotice;
use App\Models\MemberNote;
use App\Models\Club;
use Yajra\DataTables\DataTables;

trait NoticeDataTableTrait
{
    use CommonDataTableTrait;

    /**
     * Get all member notes for a specific member
     */
    public function getAllMemberNotes($request, $memberId)
    {
        $query = MemberNote::where('member_id', $memberId);

        // Search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $search = $request->search_term;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                ->orWhere('description', 'LIKE', '%' . $search . '%')
                ->orWhere('tags', 'LIKE', '%' . $search . '%')
                ->orWhere('location', 'LIKE', '%' . $search . '%');
            });
        }

        // Filter by notice_type_id
        if ($request->has('notice_type_id') && $request->notice_type_id != '') {
            $query->where('notice_type_id', $request->notice_type_id);
        }

        // Filter by poll
        if ($request->has('poll') && $request->poll != '') {
            $query->where('poll', $request->poll);
        }

        // Filter by urgency_importance
        if ($request->has('urgency_importance') && $request->urgency_importance != '') {
            $query->where('urgency_importance', $request->urgency_importance);
        }

        // Filter by comment_allowed
        if ($request->has('comment_allowed') && $request->comment_allowed != '') {
            $query->where('comment_allowed', $request->comment_allowed);
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($note) {
                return '<a href="' . route('member-notes.show', $note->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($note) {
                return $note->featured_image ? asset('storage/' . $note->featured_image) : null;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get all notice data for DataTable
     */
    public function getAllNotices($request)
    {
        $query = MemberNotice::with([ 'noticeType', 'files', 'comments', 'club', 'member',]);

        $club = Club::where('user_id', auth()->id())->first();
        $userId = auth()->id();

        if ($club) {
            $query->where('club_id', $club->id);
        } else {
            // Fall back to notices created by this user (member)
            $query->where('member_id', $userId);
        }

        // Apply search filter
        if ($request->has('search_term') && $request->search_term != '') {
            $query->where('title', 'LIKE', '%' . $request->search_term . '%');
        }

        // Sorting
        if ($request->has('order') && count($request->order)) {
            $column = $request->columns[$request->order[0]['column']]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('related_by', function ($notice) {
                if ($notice->club) {
                    return 'Club: ' . optional($notice->club)->name;
                } elseif ($notice->member) {
                    return 'Member: ' . optional($notice->member)->name;
                }
                return 'Unknown';
            })
            ->addColumn('action', function ($notice) {
                return '<a href="' . route('events.show', $notice->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('featured_image', function ($notice) {
                return $notice->featured_image
                    ? asset('storage/' . $notice->featured_image)
                    : null;
            })
            ->make(true);
    }
}
