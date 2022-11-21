<?php

namespace App\Controller;

use App\Model\Bcrypt;
use App\Model\Session;
use App\Model\UserManager;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeRequest;
use Jikan\Request\Manga\MangaRequest;

class UserController extends AbstractController
{
    /**
     * List items.
     */
    public function all_users(): string
    {
        $userManager = new UserManager();
        $users = $userManager->selectAll('name');

        return $this->twig->render('Admin/all_users.html.twig', ['users' => $users]);
    }

    public function delete(): void
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $id = trim($_POST['user.id']);
            $userManager = new UserManager();
            $userManager->selectOneById($id);

            $userManager->delete((int) $id);

            header('Location:/admin/all_users');
        }
    }

    public function add(): ?string
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            // clean $_POST data
            $user = array_map('trim', $_POST);

            // TODO validations (length, format...)

            $bcrypt = new Bcrypt(15);

            $bcrypt->hash($user['password']);

            // if validation is ok, insert and redirection
            $userManager = new UserManager();
            $lastUserId = $userManager->insert($user);

            $_SESSION['user_id'] = $lastUserId;

            $_SESSION['status'] = 'welcome to supra manga site powaa';
            header('Location:/member/user_profile?id='.$lastUserId);

            return null;
        }

        return $this->twig->render('_Modal/register.html.twig');
    }

    public function isLogout(): void
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            // //  init session

            session_unset();
            session_destroy();

            header('Location:/');
        }
    }

    public function isLogin()
    {

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $credentials = array_map('trim', $_POST);
//      @todo make some controls on email and password fields and if errors, send them to the view
            $userManager = new UserManager();
            $users = $userManager->isLogin($credentials['mail'], '');

            foreach ($users as $user){
                if ($user && password_verify($credentials['password'], $user['password'])) {
                    $_SESSION['user_id'] =  $user['id'];
                    header('Location: /member/user_profile?id='. $user['id']);
                    exit;
                }
            }
        }
        return $this->twig->render('/Member/user_profile.html.twig');
    }

    public function show_profile_user(string $id): string
    {
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            //  init session

            //$_SESSION['user_id'] = $_GET['id'];
            //die(var_dump($_SESSION['user_id']));

            //  $session->write('mail', $_GET['mail']);

            $api = new MalClient();
            if (isset($_SESSION['flash'])) {
                $session->hasFlashes($_SESSION['flash']);
            } else {
                $session = '';
            }
            if (isset($_COOKIE['anime_like'])) {
                $animeCookie = $_COOKIE['anime_like'];
                $requestAnime = $api->getAnime(new AnimeRequest($animeCookie));
            } else {
                $requestAnime = '';
            }
            if (isset($_COOKIE['manga_like'])) {
                $mangaCookie = $_COOKIE['manga_like'];
                $requestManga = $api->getManga(new MangaRequest($mangaCookie));
            } else {
                $requestManga = '';
            }

            return $this->twig->render(
                'Member/user_profile.html.twig',
                [
                    'anime_like' => $requestAnime,
                    'manga_like' => $requestManga,
                ]
            );
        }

        header('Location: /member/user_profile?id='.$id);

        return 'error';
    }

    /**
     * Edit a specific item.
     */
    public function edit_avatar(int $id)
    {
        if (isset($_POST['upload'])) {
            $uploadDir = '/public/uploads/';

            // le nom de fichier sur le serveur est celui du nom d'origine du fichier sur le poste du client (mais d'autres stratégies de nommage sont possibles)

            $uploadFile = $uploadDir.basename($_FILES['uploadfile']['name']);

            // on déplace le fichier temporaire vers le nouvel emplacement sur le serveur. Ça y est, le fichier est uploadé

            move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadFile);

            header('Location: /member/user_profile?id='.$id);
        }
    }

    public function edit(int $id): ?string
    {
        $bcrypt = new Bcrypt(15);

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            // clean $_POST data
            $user = array_map('trim', $_POST);
            $isGood = $bcrypt->verify($_POST['password'], $user['password']);

            // TODO validations (length, format...)

            // if validation is ok, update and redirection
            $userManager->update($user);

            header('Location: /member/user_profile?id='.$id);

            // we are redirecting so we don't want any content rendered
            return null;
        }

        return $this->twig->render('member/user_profile.html.twig', [
            'user' => $user,
            'password' => $isGood,
        ]);
    }
}
