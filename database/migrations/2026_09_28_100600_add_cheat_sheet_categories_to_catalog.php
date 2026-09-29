<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $categories = [
        'Portrait', 'Landscape', 'Street', 'Wedding', 'Events',
        'Sports', 'Wildlife', 'Night', 'Macro', 'Studio',
    ];

    private array $catalogTypes = [
        'competition_type', 'event_type', 'event_tag', 'judging_type', 'competition_theme',
        'competition_catagories', 'competition_voting_method', 'competition_result_method',
        'notice_type', 'news_type', 'page_type', 'max_image_per_gallery', 'max_image_file_size',
        'max_image_width', 'max_image_height', 'booking_type', 'lead_source', 'service_type',
        'deliverable_type', 'gear_category',
    ];

    private function setCatalogTypes(array $types): void
    {
        $list = implode(',', array_map(fn ($type) => "'{$type}'", $types));
        DB::statement("ALTER TABLE catalog MODIFY catalog_type ENUM({$list}) NULL");
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->setCatalogTypes([...$this->catalogTypes, 'cheat_sheet_category']);

        foreach ($this->categories as $name) {
            $exists = DB::table('catalog')
                ->where('catalog_type', 'cheat_sheet_category')
                ->where('name', $name)
                ->exists();

            if (!$exists) {
                DB::table('catalog')->insert([
                    'name'         => $name,
                    'catalog_type' => 'cheat_sheet_category',
                    'icon'         => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('catalog')
            ->where('catalog_type', 'cheat_sheet_category')
            ->delete();

        $this->setCatalogTypes($this->catalogTypes);
    }
};
