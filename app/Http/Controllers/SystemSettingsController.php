<?php

namespace App\Http\Controllers;

use App\Actions\UploadImage;
use App\Http\Requests\SystemSettingsRequest;
use App\Models\SystemInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'settings' => SystemInfo::values(),
        ]);
    }

    public function update(SystemSettingsRequest $request, UploadImage $uploadImage): RedirectResponse
    {
        SystemInfo::setValue('name', $request->validated('name'));
        SystemInfo::setValue('short_name', $request->validated('short_name'));

        $this->storeImage($request->file('img'), 'logo', [200, 200], $uploadImage);
        $this->storeImage($request->file('cover'), 'cover', [1280, 720], $uploadImage);

        return Redirect::route('settings.index')->with('success', 'System Info Successfully Updated.');
    }

    private function storeImage(?\Illuminate\Http\UploadedFile $file, string $field, array $dimensions, UploadImage $uploadImage): void
    {
        if (!$file) {
            return;
        }

        $filename = $field . '-' . time() . '.png';
        $newPath = $uploadImage->execute($file, $filename, $dimensions);
        $oldPath = SystemInfo::query()->where('meta_field', $field)->value('meta_value');
        SystemInfo::setValue($field, $newPath);
        $uploadImage->delete($oldPath);
    }
}
