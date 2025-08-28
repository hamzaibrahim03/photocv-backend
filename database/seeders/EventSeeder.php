<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Photo Events',
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
                'event_types' => [1, 2], // multiple types
                'event_tags'  => [3, 4], // multiple tags
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Video Call',
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
                'event_types' => [2], // one type
                'event_tags'  => [5, 6],
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Lecture',
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

        foreach ($events as $eventData) {
            $types = $eventData['event_types'] ?? [];
            $tags  = $eventData['event_tags'] ?? [];

            unset($eventData['event_types'], $eventData['event_tags']);

            $event = Event::create($eventData);

            if (!empty($types)) {
                $event->types()->sync($types);
            }

            if (!empty($tags)) {
                $event->tags()->sync($tags);
            }

            // Add 2 images for each event
            for ($i = 1; $i <= 2; $i++) {
                EventImage::create([
                    'event_id' => $event->id,
                    'image' => "events/event{$event->id}_image{$i}.jpg",
                    'created_by' => 1
                ]);
            }
        }
    }
}
