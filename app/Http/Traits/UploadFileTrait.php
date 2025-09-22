<?php

namespace App\Http\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait UploadFileTrait
{
    public function uploadFile($file, $fileLocation, $newName = null)
    {
        if (!file_exists($fileLocation)) {
            Storage::disk('public')->makeDirectory($fileLocation);
        }
        $hashname = Str::random(20);
        $outputFileName = $newName ?? $file->getClientOriginalName();
        $file->storePubliclyAs($fileLocation . $hashname, $outputFileName, 'public');
        return $fileLocation . $hashname . "/" . $outputFileName;
    }

    public function uploadFileStoragedisk($file, $fileLocation, $filename)
    {
        if (!file_exists($fileLocation)) {
            Storage::disk('public')->makeDirectory($fileLocation);
        }
        $hashname = Str::random(20);
        $outputFile = $fileLocation . $hashname . "/" . $filename;
        Storage::disk('public')->put($outputFile, $file);
        return $outputFile;
    }

    public function replaceUploadFile($oldFile, $file, $fileLocation)
    {
        $oldfileUniqueFolder = explode("/", $oldFile);
        array_pop($oldfileUniqueFolder);
        Storage::deleteDirectory("public/" . implode("/", $oldfileUniqueFolder)); // DELETE OLD FILE
        $hashname = Str::random(20);
        $outputFileName = $file->getClientOriginalName();
        $file->storePubliclyAs($fileLocation . $hashname, $outputFileName, 'public');
        return $fileLocation . $hashname . "/" . $outputFileName;
    }
}
