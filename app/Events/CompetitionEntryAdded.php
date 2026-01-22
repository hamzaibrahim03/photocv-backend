<?php

namespace App\Events;

use App\Models\CompetitionMembersEntry;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitionEntryAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CompetitionMembersEntry $entry,
        public User $actor
    ) {}
}
