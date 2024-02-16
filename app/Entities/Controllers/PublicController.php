<?php

namespace BookStack\Entities\Controllers;

use BookStack\Activity\Tools\UserEntityWatchOptions;
use BookStack\Entities\Repos\PageRepo;
use BookStack\Entities\Tools\PageContent;
use BookStack\Http\Controller;

class PublicController extends Controller
{
    public function __construct(
        protected PageRepo $pageRepo,
    ) {
    }

    /**
     * Provide an image file from storage.
     *
     * @throws NotFoundException
     */
    public function show(string $path)
    {
        $page = $this->pageRepo->getByPublicPath($path);
        if (is_null($page)) {
            return view('public.not-found', []);
        }

        $this->checkOwnablePermission('page-view', $page);

        $pageContent = (new PageContent($page));
        $page->html = $pageContent->render();
        $pageNav = $pageContent->getNavigation($page->html);

        $this->setPageTitle($page->getShortName());

        return view('public.show', [
            'page'            => $page,
            'book'            => $page->book,
            'current'         => $page,
            'pageNav'         => $pageNav,
            'watchOptions'    => new UserEntityWatchOptions(user(), $page),
            'referenceCount'  => 0
        ]);
    }
}
