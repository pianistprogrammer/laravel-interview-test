<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use TitlesService;
use App\Http\Controllers\Controller;


class MovieController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */

     private $titlesService;

    public function __construct(TitlesService $titlesService) {
        $this->titlesService = $titlesService;
    }   

    public function getTitles(): JsonResponse
    {
        $allTitles = $this->titlesService->getTitles();

        if (empty($allTitles)) {
                return response()->json(['status' => 'failure']);
        }

        return response()->json(['titles' => $allTitles]);
    }

}
