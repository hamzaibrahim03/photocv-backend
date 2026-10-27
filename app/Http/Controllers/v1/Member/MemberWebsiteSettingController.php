<?php

namespace App\Http\Controllers\v1\Member;

use App\Http\Controllers\Controller;
use App\Http\Responses\MemberResponse;
use App\Models\MemberWebsiteSetting;
use Illuminate\Http\Request;

class MemberWebsiteSettingController extends Controller
{
    /**
     * A single free-form JSON blob per member backs every tab of the
     * photographer Settings/Signup wizard (general, appearance, content
     * management, privacy, account-profile-fields). There is no bespoke
     * column-per-field schema for this yet, so the whole settings object
     * is read/replaced as one unit.
     */
    public function show()
    {
        $record = MemberWebsiteSetting::where('member_id', auth()->id())->first();
        return MemberResponse::success('Settings retrieved successfully.', $record->settings ?? (object) []);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $record = MemberWebsiteSetting::updateOrCreate(
            ['member_id' => auth()->id()],
            ['settings' => $validated['settings']]
        );

        return MemberResponse::success('Settings saved successfully.', $record->settings);
    }
}
