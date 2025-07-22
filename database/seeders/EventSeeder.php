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
                'name' => 'Annual Photography Meetup',
                'event_date' => Carbon::now()->addDays(10),
                'description' => 'A gathering of club photographers to share and learn.',
                'event_type_id' => 1,
                'event_kind_id' => 1,
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
                'updated_by' => 1
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Editing Masterclass',
                'event_date' => Carbon::now()->addDays(20),
                'description' => 'Learn photo editing techniques from the pros.',
                'event_type_id' => 2,
                'event_kind_id' => 1,
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
                'updated_by' => 1
            ],
            [
                'club_id' => 1,
                'featured_image' => null,
                'name' => 'Street Photography Walk',
                'event_date' => Carbon::now()->addDays(30),
                'description' => 'Explore the city and capture the urban life.',
                'event_type_id' => 1,
                'event_kind_id' => 2,
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
                'updated_by' => 1
            ]
        ];

        foreach ($events as $eventData) {
            $event = Event::create($eventData);

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
