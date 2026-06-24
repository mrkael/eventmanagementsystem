<?php

namespace App\Services\Core;

use App\Models\OrganiserProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OrganiserProfileService
{
    public function create(array $data, int $userId): OrganiserProfile
    {
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $data['logo_path'] = $this->storeLogo($data['logo'] ?? null);
        unset($data['logo']);

        return OrganiserProfile::create($data);
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

        return $organiser->fresh(['creator', 'updater']);
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
