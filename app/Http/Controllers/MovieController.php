<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use External\Bar\Movies\MovieService as BarService;
use External\Baz\Movies\MovieService as BazService;
use External\Foo\Movies\MovieService as FooService;
use External\Bar\Exceptions\ServiceUnavailableException;


class MovieController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    private $maxRetries = 3;
    private $retryDelay = 1; // in seconds
    private $cache;
    private $barService;
    private $fooService;
    private $bazService;

    public function __construct(Cache $cache, BarService $barService, FooService $fooService, BazService $bazService) {
        $this->cache = $cache;
        $this->barService = $barService;
        $this->fooService = $fooService;
        $this->bazService = $bazService;
        $this->maxRetries = $maxRetries;
        $this->retryDelay = $retryDelay;
        
    }

    public function getTitles(Request $request): JsonResponse
    {
        $allTitles = [];
        $retries = 0;

        if ($this->cache->has('titles')) {
            return $this->cache->get('titles');
        }
        while ($retries < $this->maxRetries) {
            try {
                $barMovieTitles = $this->barService->getTitles();
                $fooMovieTitles = $this->fooService->getTitles();
                $bazMovieTitles = $this->bazService->getTitles();

                //ensuring only one title from each array of titles
                $barTitles = array_slice(array_column($barMovieTitles['titles'], 'title'), 0, 1);
                $fooTitles = array_slice($fooMovieTitles, 0, 1);
                $bazTitles = array_slice($bazMovieTitles, 0, 1);

                $allTitles = array_merge($barTitles, $fooTitles, $bazTitles);
                break;
            } catch (ServiceUnavailableException $e) {

                // building resillience pattern of retry
                $retries++;
                sleep($this->retryDelay);
                return reponse()->json(['status' => 'failure']);
            }
        }

        if ($retries === $this->maxRetries) {
            return reponse()->json(['status' => 'failure']);
        }
        // setting the titles to cache for future retrieval
        $this->cache->set('titles', $allTitles);
        return array_unique($allTitles);
        
    }

}
