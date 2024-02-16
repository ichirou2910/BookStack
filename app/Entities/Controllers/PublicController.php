<?php

namespace BookStack\Entities\Controllers;

use BookStack\Activity\Tools\UserEntityWatchOptions;
use BookStack\Entities\Repos\PageRepo;
use BookStack\Entities\Tools\PageContent;
use BookStack\Http\Controller;
use Exception;

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
        try {
            $page = $this->pageRepo->getByPublicPath($path);
        } catch (Exception $e) {
            return view('public.not-found', []);
        }

        $this->checkOwnablePermission('page-view', $page);

        $pageContent = (new PageContent($page));
        $page->html = $pageContent->render();
        $pageNav = $pageContent->getNavigation($page->html);

        /* $sidebarTree = (new BookContents($page->book))->getTree(); */
        /* $commentTree = (new CommentTree($page)); */
        /* $nextPreviousLocator = new NextPreviousContentLocator($page, $sidebarTree); */

        /* View::incrementFor($page); */
        $this->setPageTitle($page->getShortName());

        return view('public.show', [
            'page'            => $page,
            'book'            => $page->book,
            'current'         => $page,
            /* 'sidebarTree'     => $sidebarTree, */
            /* 'commentTree'     => $commentTree, */
            'pageNav'         => $pageNav,
            'watchOptions'    => new UserEntityWatchOptions(user(), $page),
            /* 'next'            => $nextPreviousLocator->getNext(), */
            /* 'previous'        => $nextPreviousLocator->getPrevious(), */
            'referenceCount'  => 0
        ]);
    }
}
