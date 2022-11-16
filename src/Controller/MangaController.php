<?php

namespace App\Controller;

use App\Model\ArticleManager;
use App\Model\UserManager;
use JasonGrimes\Paginator;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaRequest;
use Jikan\Request\Top\TopMangaRequest;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

class MangaController extends AbstractController
{
    /**
     * List items.
     */
    public function listManga(): string
    {
        if (isset($_SESSION['user_id'])) {
            $userManager = new UserManager();
            $user_profile = $userManager->selectOneById($_SESSION['user_id']);
        } else {
            $user_profile = '';
        }

        $articleManager = new ArticleManager();
        $articles = $articleManager->selectAll('title');

        $apiManga = new MalClient();

        $topManga = $apiManga->getTopManga(new TopMangaRequest(1, 'manga'));

        $manga = $topManga->getResults();

        $adapter = new ArrayAdapter($manga);

        $pagerfanta = new Pagerfanta($adapter);

        // pagination

        $totalItems = 1000;

        $itemsPerPage = 10;

        $currentPage = 8;
        $urlPattern = '/foo/page/(:num)';

        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

        $num = $paginator->getPages();

        // end pagination

        $maxPerPage = 10;
        $pagerfanta->setMaxPerPage($maxPerPage); // 10 by default
        $maxPerPage = $pagerfanta->getMaxPerPage();
        $pagerfanta->setCurrentPage($currentPage); // 1 by default
        $currentPage = $pagerfanta->getCurrentPage();

        $nbResults = $pagerfanta->getNbResults();
        $currentPageResults = $pagerfanta->getCurrentPageResults();

        return $this->twig->render('Manga/manga.html.twig', ['manga_list' => $manga,
            'article' => $articles,
            'paginator' => $paginator,
            'num' => $num,
            'pagerfunta' => $pagerfanta,
        ]);
    }

    // show unique page info manga
         public function showMangaMoreInfo(int $malId): string
         {
             if (isset($_SESSION['id'])) {
                 $userManager = new UserManager();
                 $user_profile = $userManager->selectOneById($_SESSION['id']);
             } else {
                 $user_profile = 'en';
             }

             $apiAnime = new MalClient();

             $data = $apiAnime->getManga(new MangaRequest($malId));

             return $this->twig->render('Manga/show.html.twig', ['manga_show' => $data,
             ]);
         }
}
