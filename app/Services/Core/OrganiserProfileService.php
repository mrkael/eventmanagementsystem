<?php

namespace App\Services\Core;

use App\Models\OrganiserProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganiserProfileService
{
    public array $lastLoginAccessResult = ['sent' => false, 'message' => 'Login access email was not sent.'];

    public function __construct(private OrganiserLoginAccessService $loginAccessService) {}

    public function create(array $data, int $userId): OrganiserProfile
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;
            $data['logo_path'] = $this->storeLogo($data['logo'] ?? null);
            unset($data['logo']);

            $profile = OrganiserProfile::create($data);
            $this->lastLoginAccessResult = $this->loginAccessService->ensureAccess($profile);

            return $profile->fresh(['user', 'creator', 'updater']);
        });
    }

    public function update(OrganiserProfile $organiser, array $data, int $userId): OrganiserProfile
    {
        $data['updated_by'] = $userId;

        if (($data['logo'] ?? null) instanceof UploadedFile) {
            if ($organiser->logo_path) {
                Storage::disk('public')->delete($organiser->logo_path);
            }
            $data['logo_path'] = $this->storeLogo($data['logo']);
        }

        unset($data['logo']);
        $organiser->update($data);
        if ($organiser->user) {
            $organiser->user->forceFill([
                'name' => $organiser->user->name ?: $organiser->name,
                'email' => $organiser->email,
            ])->save();
        }

        return $organiser->fresh(['user', 'creator', 'updater']);
    }

    public function delete(OrganiserProfile $organiser): void
    {
        $organiser->delete();
    }

    private function storeLogo(?UploadedFile $logo): ?string
    {
        return $logo instanceof UploadedFile ? $logo->store('organisers/logos', 'public') : null;
    }
}
