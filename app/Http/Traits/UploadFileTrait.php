<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

trait UploadFileTrait
{
    public function uploadFile($file, $fileLocation, $newName = null)
    {
        $hashmake = Hash::make('secret');
        $hashname = substr(hash('sha256', $hashmake), 0, 20);
        $originalName = $newName ?? $file->getClientOriginalName();
        $file->storePubliclyAs($fileLocation . $originalName, 'public');
        return $fileLocation . $hashname . "/" . $originalName;
    }

    public function replaceUploadFile($oldFile, $file, $fileLocation)
    {
        $oldAttachment = explode("/", $oldFile);
        $hashmake = Hash::make('secret');
        $hashname = substr(hash('sha256', $hashmake), 0, 20);
        $originalName = $file->getClientOriginalName();
        $file->storePubliclyAs($fileLocation . $hashname, $originalName, 'public');
        // FILE LOCATION MUST FOLLOW THE SAME STRUCTURE OF public/*/*/*hashedname/*originalname
        // NOT TESTED MIGHT DELETE ANOTHER FILE
        Storage::deleteDirectory("public/" . $oldAttachment[0] . "/" . $oldAttachment[1] . "/" . $oldAttachment[2]);
        return $fileLocation . $hashname . "/" . $originalName;
    }
}
