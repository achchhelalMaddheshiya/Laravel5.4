<?php
namespace App\Interfaces;

use Illuminate\Http\Request;
interface UploadInterface {
    public function Upload(Request $request);
}