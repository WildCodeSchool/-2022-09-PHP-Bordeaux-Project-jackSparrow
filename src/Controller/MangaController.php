<?php

namespace App\Controller;

use App\Model\ArticleManager;
use App\Model\UserManager;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Top\TopMangaRequest;

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

        return $this->twig->render('Manga/manga.html.twig', ['manga_list' => $manga,
            'article' => $articles,
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

    // search manga
    public function searchManga(string $query): ?string
    {
        if (isset($_SESSION['id'])) {
            $userManager = new UserManager();
            $user_profile = $userManager->selectOneById($_SESSION['id']);
        } else {
            $user_profile = 'end';
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $query = array_map('trim', $_POST);
        }

        $apiManga = new MalClient();

        $mangaSearchResults = $apiManga->getMangaSearch(new MangaSearchRequest((string) $query));

        return $this->twig->render('Manga/search.html.twig', ['manga_search' => $mangaSearchResults,
            'found' => $query,
        ]);
    }
}
