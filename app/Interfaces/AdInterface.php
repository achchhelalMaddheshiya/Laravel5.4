<?php
namespace App\Interfaces;

use Illuminate\Http\Request;
interface AdInterface {
    public function getAds(Request $request);

    public function createAd(Request $request);

    public function saveAdStats(Request $request);

}