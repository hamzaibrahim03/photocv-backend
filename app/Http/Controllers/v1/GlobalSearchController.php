<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClubNews;
use App\Models\Event;
use App\Models\Competition;

class GlobalSearchController extends Controller
{
     public function search(Request $request)
    {
        $term = $request->get('q');

        if (!$term) {
            return response()->json([
                'success' => true,
                'message' => 'No search term provided',
                'data' => []
            ]);
        }

        $results = collect();

        $clubNews = ClubNews::where('title', 'LIKE', "%{$term}%")
            ->select('id', 'title')
            ->limit(5)
            ->get()
            ->map(fn ($news) => [
                'id' => $news->id,
                'type' => 'club_news',
                'title' => $news->title,
                'url' => route('club-news.show', $news->id),
            ]);

        $events = Event::where('name', 'LIKE', "%{$term}%")
            ->select('id', 'name')
            ->limit(5)
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'type' => 'event',
                'name' => $event->name,
                'url' => route('events.show', $event->id),
            ]);

        $competitions = Competition::where('name', 'LIKE', "%{$term}%")
            ->select('id', 'club_id', 'name')
            ->limit(5)
            ->get()
            ->map(fn ($comp) => [
                'id' => $comp->id,
                'type' => 'competition',
                'title' => $comp->name,
                'club' => $comp->club?->summary(),
                'url' => route('competitions.show', $comp->id),
            ]);

        $results = $results->merge($clubNews)->merge($events)->merge($competitions);

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully',
            'data' => $results->values()
        ]);
    }
}
