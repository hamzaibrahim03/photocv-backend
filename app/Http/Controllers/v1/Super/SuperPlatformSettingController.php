<?php

namespace App\Http\Controllers\v1\Super;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class SuperPlatformSettingController extends Controller
{
    public function show()
    {
        return MemberResponse::success('Platform settings retrieved successfully.', $this->current());
    }

    public function update(Request $request)
    {
        $settings = $this->current();
        $settings->update($request->only((new PlatformSetting())->getFillable()));

        return MemberResponse::success('Platform settings updated successfully.', $settings->fresh());
    }

    private function current(): PlatformSetting
    {
        $settings = PlatformSetting::first();
        if ($settings) {
            return $settings;
        }

        $settings = PlatformSetting::create([]);
        return $settings->fresh();
    }
}
