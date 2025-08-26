<?php

namespace App\Traits\DataTables;

use Yajra\DataTables\DataTables;
use App\Models\Gallery;
use App\Models\User;
use App\Models\MemberAward;
use App\Models\MemberBrand;
use App\Models\MemberPracticeLog;

trait MemberDataTableTrait
{
    use CommonDataTableTrait;

    /**
     * Get all pending member requests for the authenticated user's club
     */
    public function getAllMemberPendingRequests($request)
    {
        $club = auth()->user()->club;

        $query = $club->users()
            ->select('users.*')
            ->wherePivot('status', 'pending')
            ->with(['socialLinks']);

        if ($request->filled('search_term')) {
            $query->where(function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search_term . '%')
                ->orWhere('email', 'like', '%' . $request->search_term . '%')
                ->orWhere('first_name', 'like', '%' . $request->search_term . '%')
                ->orWhere('last_name', 'like', '%' . $request->search_term . '%')
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->search_term}%"]);
            });
        }

        $totalGalleries = Gallery::whereHas('member.clubs', function ($query) use ($club) {
                $query->where('club_id', $club->id)
                    ->where('status', 'approved');
            })
            ->where('is_active', true)
            ->count();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('actions', function ($user) {
                return '
                    <button class="btn btn-success btn-sm approve-request" data-id="'.$user->id.'">Approve</button>
                    <button class="btn btn-danger btn-sm reject-request" data-id="'.$user->id.'">Reject</button>
                ';
            })
            ->with('total_galleries', $totalGalleries)
            ->rawColumns(['social_links', 'actions'])
            ->make(true);
    }

    /**
     * Get all members for the authenticated user's club with additional stats
     */
    public function getAllMemberIndexData($request)
    {
        $clubId = auth()->user()->club->id;
        $query = User::with(['roles', 'socialLinks'])
        ->whereHas('clubs', function ($query) use ($clubId) {
            $query->where('club_id', $clubId)
                  ->where('status', 'approved');
        })
        ->with([
            'galleries' => function ($query) {
                $query->where('is_active', true)
                    ->with(['photos' => function ($photoQuery) {
                        $photoQuery->where('is_active', true)
                            ->with(['comments' => function ($q) {
                                $q->where('is_published', true);
                            }]);
                    }]);
            }
        ]);

        // Search
        if ($request->filled('search_term')) {
            $query->where('username', 'like', '%' . $request->search_term . '%');
        }

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('gallery_total_photos', function ($member) {
                return $member->galleries->sum(function ($gallery) {
                    return $gallery->photos->count();
                });
            })

            ->addColumn('gallery_total_comments', function ($member) {
                return $member->galleries->sum(function ($gallery) {
                    return $gallery->photos->sum(function ($photo) {
                        return $photo->comments->count();
                    });
                });
            })

            ->addColumn('gallery_total_likes', function ($member) {
                $likes = 0;
                foreach ($member->galleries as $gallery) {
                    foreach ($gallery->photos as $photo) {
                        $likes += $photo->comments->where('comment_type', 'liking')->count();
                    }
                }
                return $likes;
            })

            ->addColumn('award_winning_photos', function ($member) {
                return MemberAward::whereHas('photo.gallery', function ($query) use ($member) {
                    $query->where('uploaded_by', $member->id);
                })->count();
            })

            // ->addColumn('comments_preview', function ($member) {
            //     $comments = [];

            //     foreach ($member->galleries as $gallery) {
            //         foreach ($gallery->photos as $photo) {
            //             foreach ($photo->comments as $comment) {
            //                 if ($comment->comment_type === 'comment') {
            //                     $comments[] = $comment->comment;
            //                     if (count($comments) >= 3) break 3;
            //                 }
            //             }
            //         }
            //     }

            //     return implode('<br>', $comments);
            // })

            // ->rawColumns(['comments_preview'])
        ->make(true);
    }

    /**
     * Get all member interests and brands with filtering and pagination.
     */
    public function getAllMemberInterestsBrands($request, $memberId)
    {
        $query = MemberBrand::where('member_id', $memberId);

        // Filter by type
        if ($request->has('type') && in_array($request->type, ['interest', 'brands'])) {
            $query->whereNotNull($request->type);
        }

        // Search term filter
        if ($request->has('search_term') && $request->search_term !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('interest', 'like', '%' . $request->search_term . '%')
                ->orWhere('brands', 'like', '%' . $request->search_term . '%');
            });
        }

        // Order by column if provided (DataTables)
        if ($request->has('order') && count($request->order)) {
            $columnIndex = $request->order[0]['column'];
            $column = $request->columns[$columnIndex]['data'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($column, $direction);
        }

        // Get results
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                return '<a href="' . route('events.show', $item->id) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->editColumn('image', function ($item) {
                return $item->image ? asset('storage/' . $item->image) : null;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get practice logs for the authenticated member with filtering and pagination.
     */
    public function getDataTableResponse($request, $type)
    {
        $query = MemberPracticeLog::where('member_id', auth()->id())
            ->where('type', $type);

        if ($request->has('search_term') && $request->search_term !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search_term . '%')
                ->orWhere('description', 'like', '%' . $request->search_term . '%');
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('file', function ($log) {
                return $log->file ? asset('storage/' . $log->file) : null;
            })
            ->addColumn('action', function ($log) {
                if ($log->file) {
                    return '<a href="' . asset('storage/' . $log->file) . '" target="_blank" class="btn btn-sm btn-primary">View</a>';
                }
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
