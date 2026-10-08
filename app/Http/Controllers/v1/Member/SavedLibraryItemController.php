<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Models\SavedLibraryItem;
use Illuminate\Http\Request;

class SavedLibraryItemController extends Controller
{
    /**
     * List the current user's saved library items, optionally filtered by category.
     */
    public function index(Request $request)
    {
        $query = SavedLibraryItem::where('user_id', auth()->id());

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        $items = $query->orderByDesc('created_at')->get();

        return MemberResponse::success('Saved library items retrieved successfully.', $items);
    }

    /**
     * Toggle a bookmark: creates it if not already saved, removes it if it is.
     * Mirrors the existing frontend's toggleBookmark semantics (one request per click).
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|in:Events,Competitions,Galleries,Notices,News',
            'item_id' => 'required|integer',
            'title' => 'nullable|string|max:250',
            'description' => 'nullable|string',
            'date' => 'nullable|string|max:50',
            'image' => 'nullable|string|max:250',
        ]);

        $existing = SavedLibraryItem::where('user_id', auth()->id())
            ->where('category', $request->input('category'))
            ->where('item_id', $request->input('item_id'))
            ->first();

        if ($existing) {
            $existing->delete();
            return MemberResponse::success('Removed from saved library.', ['saved' => false]);
        }

        $item = SavedLibraryItem::create([
            'user_id' => auth()->id(),
            'category' => $request->input('category'),
            'item_id' => $request->input('item_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'date' => $request->input('date'),
            'image' => $request->input('image'),
        ]);

        return MemberResponse::success('Added to saved library.', ['saved' => true, 'item' => $item], 201);
    }

    /**
     * Remove a saved item by its saved_library_items id.
     */
    public function destroy(string $id)
    {
        $item = SavedLibraryItem::where('user_id', auth()->id())->findOrFail($id);
        $item->delete();

        return MemberResponse::success('Removed from saved library.', null);
    }
}
