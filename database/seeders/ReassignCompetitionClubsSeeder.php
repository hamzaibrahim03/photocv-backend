<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\ClubSeason;
use App\Models\Competition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReassignCompetitionClubsSeeder extends Seeder
{
    /**
     * Spread existing competitions across all clubs (round-robin by id),
     * instead of every competition belonging to the first club.
     * Also gives clubs without seasons a copy of the first club's seasons,
     * since results derive season_name from the owning club's seasons.
     *
     * Run: php artisan db:seed --class=ReassignCompetitionClubsSeeder
     */
    public function run(): void
    {
        $clubIds = Club::orderBy('id')->pluck('id')->values();

        if ($clubIds->count() < 2) {
            $this->command?->warn('Fewer than 2 clubs found; run ClubSeeder first.');
            return;
        }

        DB::transaction(function () use ($clubIds) {
            $competitions = Competition::withTrashed()->orderBy('id')->get(['id', 'name', 'club_id']);

            foreach ($competitions as $index => $competition) {
                $newClubId = $clubIds[$index % $clubIds->count()];

                if ($competition->club_id !== $newClubId) {
                    $this->command?->line("Competition #{$competition->id} \"{$competition->name}\": club {$competition->club_id} -> {$newClubId}");
                    // Query-builder update: skips model events and leaves updated_at untouched
                    Competition::withTrashed()->whereKey($competition->id)->toBase()->update(['club_id' => $newClubId]);
                }
            }

            $templateSeasons = ClubSeason::where('club_id', $clubIds->first())->get();

            foreach ($clubIds->slice(1) as $clubId) {
                if (ClubSeason::where('club_id', $clubId)->exists()) {
                    continue;
                }

                foreach ($templateSeasons as $season) {
                    ClubSeason::create([
                        'club_id'    => $clubId,
                        'name'       => $season->name,
                        'status'     => $season->status,
                        'start_date' => $season->start_date,
                        'end_date'   => $season->end_date,
                    ]);
                }
            }
        });
    }
}
