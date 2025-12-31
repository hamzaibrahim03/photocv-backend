<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Services\Image\ImageResizeService;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Light Painting',
                'event_date' => Carbon::now()->addDays(10),
                'description' => 'A gathering of club photographers to share and learn.',
                'duration' => '2 hours',
                'speaker' => 'John Doe',
                'speaker_club' => 'Nature Clickers',
                'speaker_qualification' => 'Wildlife Photographer',
                'status' => 'scheduled',
                'required_gear' => 'DSLR, Tripod',
                'tags_keywords' => 'photography, meetup, club',
                'url' => 'https://example.com/event1',
                'rsvp_detail' => 'Register online',
                'enable_dropbox_upload' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'event_types' => [1, 2],
                'event_tags'  => [3, 4],
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'A night with Mick',
                'event_date' => Carbon::now()->addDays(20),
                'description' => 'Learn photo editing techniques from the pros.',
                'duration' => '3 hours',
                'speaker' => 'Jane Smith',
                'speaker_club' => 'Photo Experts',
                'speaker_qualification' => 'Adobe Certified Expert',
                'status' => 'scheduled',
                'required_gear' => 'Laptop with Photoshop',
                'tags_keywords' => 'editing, photoshop, learning',
                'url' => 'https://example.com/event2',
                'rsvp_detail' => 'Email RSVP',
                'enable_dropbox_upload' => false,
                'created_by' => 1,
                'updated_by' => 1,
                'event_types' => [2],
                'event_tags'  => [5, 6],
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'A Photographer Dreamland',
                'event_date' => Carbon::now()->addDays(30),
                'description' => 'Explore the city and capture the urban life.',
                'duration' => '1 day',
                'speaker' => 'Mike Urban',
                'speaker_club' => 'Urban Clicks',
                'speaker_qualification' => 'Street Photographer',
                'status' => 'scheduled',
                'required_gear' => 'Mirrorless Camera',
                'tags_keywords' => 'street, walk, photography',
                'url' => 'https://example.com/event3',
                'rsvp_detail' => 'Google Form',
                'enable_dropbox_upload' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'event_types' => [1],
                'event_tags'  => [7, 8],
            ]
        ];

        $resizeService = app(ImageResizeService::class);

        foreach ($events as $eventData) {

            $types = $eventData['event_types'] ?? [];
            $tags  = $eventData['event_tags'] ?? [];

            unset($eventData['event_types'], $eventData['event_tags']);

            $event = Event::create($eventData);

            if ($types) {
                $event->types()->sync($types);
            }

            if ($tags) {
                $event->tags()->sync($tags);
            }

            /**
             * 📸 ADD EVENT IMAGES WITH SIZES
             */
            for ($i = 1; $i <= 2; $i++) {

                // Source seed image
                $sourcePath = database_path("seeders/data/events/event{$i}.jpg");

                if (!File::exists($sourcePath)) {
                    continue; // safe skip
                }

                // Store ORIGINAL
                $filename = uniqid('event_') . '.jpg';
                $originalPath = "events/original/{$filename}";

                Storage::disk('public')->put(
                    $originalPath,
                    File::get($sourcePath)
                );

                // Generate sizes (thumb / medium / large)
                $resizeService->generateSizes(
                    $originalPath,
                    'events'
                );

                // Save DB record
                EventImage::create([
                    'event_id'   => $event->id,
                    'image'      => $originalPath,
                    'created_by' => 1,
                ]);
            }
        }
    }
}
