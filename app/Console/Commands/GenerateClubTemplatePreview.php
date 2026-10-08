<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\ClubSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class GenerateClubTemplatePreview extends Command
{
    protected $signature = 'app:generate-club-template-preview {club? : Club ID to generate for; omit for all clubs}';

    protected $description = 'Capture a real screenshot of a club\'s public page for the Super admin "Template" preview column';

    /**
     * The only club whose public page currently resolves to real content in
     * the React app: ClubPublicHome.jsx (and the rest of src/Ryton/) still
     * hardcode the "rytonlocal" tenant's API host rather than deriving it
     * from window.location.hostname, so every other club has no working
     * public URL to screenshot yet.
     */
    private const WORKING_USERNAME = 'rytonlocal';
    private const FRONTEND_URL = 'http://localhost:5173/club-public';

    public function handle(): int
    {
        $clubId = $this->argument('club');
        $clubs = $clubId
            ? Club::with('user')->where('id', $clubId)->get()
            : Club::with('user')->get();

        foreach ($clubs as $club) {
            $this->generateForClub($club);
        }

        return self::SUCCESS;
    }

    private function generateForClub(Club $club): void
    {
        if (($club->user?->username) !== self::WORKING_USERNAME) {
            $this->line("Skipping \"{$club->club_name}\" (id {$club->id}): its public page isn't wired up in the frontend yet.");
            return;
        }

        $relativePath = "club-templates/{$club->id}.png";
        $absolutePath = Storage::disk('public')->path($relativePath);
        Storage::disk('public')->makeDirectory('club-templates');

        $script = base_path('screenshot-tool/shot.js');

        $this->info("Capturing screenshot for \"{$club->club_name}\"...");
        $result = Process::timeout(60)->run([
            'C:\\Program Files\\nodejs\\node.exe',
            $script,
            self::FRONTEND_URL,
            $absolutePath,
        ]);

        if (!$result->successful()) {
            $this->error("Screenshot capture failed for club {$club->id}: " . $result->errorOutput());
            return;
        }

        $setting = $club->setting ?? new ClubSetting(['club_id' => $club->id]);
        $setting->template_preview = $relativePath;
        $setting->template_preview_generated_at = now();
        $setting->save();

        $this->info("Saved preview to storage/app/public/{$relativePath}");
    }
}
