<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

trait UploadFileTrait
{
    protected $fileTypeDirectories = [
        'BANK DETAILS' => 'supplier/uploads/bank_details/',
        'CERTIFICATE OF REGISTRATION WITH SEC/DTI REGISTRATION' => 'supplier/uploads/registration/',
        'CITY/MUNICIPAL PERMIT' => 'supplier/uploads/permits/',
        'BIR 2303 CERTIFICATE OF REGISTRATION' => 'supplier/uploads/bir_certificates/',
        'CERTIFICATE OF PRODUCT/MSDS' => 'supplier/uploads/msds/',
        'CERTIFICATE OF DELEARSHIP/DISTRIBUTORSHIP' => 'supplier/uploads/dealership/',
        'DENR PERMITS' => 'supplier/uploads/denr_permits/',
        'TRADE TEST RESULTS' => 'supplier/uploads/trade_test/',
        'PRICE LIST/QUOTATION' => 'supplier/uploads/quotation/',
        'OTHERS' => 'supplier/uploads/others/',
    ];

    private function generateHashName()
    {
        $hashmake = Hash::make('secret');
        return substr(hash('sha256', $hashmake), 0, 20);
    }

    private function getFileLocation($fileType)
    {
        return $this->fileTypeDirectories[$fileType] ?? 'supplier/uploads/others/';
    }

    public function uploadFile($file, $fileType, $newName = null)
    {
        $fileLocation = $this->getFileLocation($fileType);
        $hashName = $this->generateHashName();
        $originalName = $newName ?? $file->getClientOriginalName();
        $filePath = $fileLocation . $hashName . "/" . $originalName;
        Storage::putFileAs($fileLocation . $hashName, $file, $originalName);
        return $filePath;
    }

    public function replaceUploadFile($oldFile, $file, $fileType)
    {
        $fileLocation = $this->getFileLocation($fileType);
        $oldAttachment = explode("/", $oldFile);
        $hashName = $this->generateHashName();
        $originalName = $file->getClientOriginalName();
        $filePath = $fileLocation . $hashName . "/" . $originalName;
        Storage::putFileAs($fileLocation . $hashName, $file, $originalName);

        // Delete old file directory
        Storage::deleteDirectory(implode("/", array_slice($oldAttachment, 0, 3)));
        return $filePath;
    }
}
