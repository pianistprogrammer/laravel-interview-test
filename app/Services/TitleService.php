<?php

use Illuminate\Support\Facades\Cache;
use External\Bar\Movies\MovieService as BarService;
use External\Baz\Movies\MovieService as BazService;
use External\Foo\Movies\MovieService as FooService;
use External\Bar\Exceptions\ServiceUnavailableException;


class TitlesService
{
    private $barService;
    private $fooService;
    private $bazService;
    private $cache;
    private $maxRetries = 3;
    private $retryDelay = 1;

    public function __construct(BarService $barService, FooService $fooService, BazService $bazService, Cache $cache)
    {
        $this->barService = $barService;
        $this->fooService = $fooService;
        $this->bazService = $bazService;
        $this->cache = $cache;
    }

    public function getTitles(): array
    {
        $allTitles = [];
        $services = [$this->barService, $this->fooService, $this->bazService];

        if ($this->cache->has('titles')) {
            return $this->cache->get('titles');
        }

        foreach ($services as $service) {
            try {
                $movieTitles = $service->getTitles();
                $titles = [];

                switch (get_class($service)) {
                    case BarService::class:
                        $titles = array_slice(array_column($movieTitles['titles'], 'title'), 0, 1);
                        break;
                    default:
                        $titles = array_slice($movieTitles, 0, 1);
                        break;
                }

                $allTitles = array_merge($allTitles, $titles);
                break;
            } catch (ServiceUnavailableException $e) {
                continue;
            }
        }

        if (empty($allTitles)) {
            return [];
        }
        $this->cache->set('titles', $allTitles);
        return array_unique($allTitles);
    }
}
