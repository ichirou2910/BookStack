<?php

namespace BookStack\Access\Guards;

use BookStack\Access\RegistrationService;
use BookStack\Access\TruesightDevopsService;
use BookStack\Permissions\PermissionsRepo;
use BookStack\Users\UserRepo;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;

class TruesightDevopsSessionGuard extends ExternalBaseSessionGuard
{
    protected TruesightDevopsService $truesightDevopsService;
    protected PermissionsRepo $permissionsRepo;
    protected UserRepo $userRepo;

    /**
     * TruesightDevopsSessionGuard constructor.
     */
    public function __construct(
        $name,
        UserProvider $provider,
        Session $session,
        RegistrationService $registrationService,
        TruesightDevopsService $truesightDevopsService,
        PermissionsRepo $permissionsRepo,
        UserRepo $userRepo,
    ) {
        $this->truesightDevopsService = $truesightDevopsService;
        $this->userRepo = $userRepo;
        $this->permissionsRepo = $permissionsRepo;
        parent::__construct($name, $provider, $session, $registrationService);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @param bool  $remember
     *
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false): bool
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        $devopsUser = $this->truesightDevopsService->getInfo($username, $password);
        if ($devopsUser == null) {
            return false;
        }
        $credentials['name'] = $devopsUser->name;
        $user = $this->userRepo->getByUsername($username);
        if ($user == null) {
            // Create the user
            $newUser = $this->userRepo->createWithoutActivity($credentials, true);

            // Create & assign user-specific role
            $roleData = [
                'display_name' => $username,
                'description' => 'Role for ' . $username,
                'mfa_enforced' => false,
                'permissions' => [
                    'attachment-create-all',
                    'attachment-delete-all',
                    'attachment-delete-own',
                    'attachment-update-all',
                    'attachment-update-own',
                    'book-view-all',
                    'book-view-own',
                    'bookshelf-view-all',
                    'bookshelf-view-own',
                    'chapter-view-all',
                    'chapter-view-own',
                    'comment-create-all',
                    'comment-delete-all',
                    'comment-delete-own',
                    'comment-update-all',
                    'comment-update-own',
                    'content-export',
                    'image-create-all',
                    'image-delete-all',
                    'image-delete-own',
                    'image-update-all',
                    'image-update-own',
                    'page-view-all',
                    'page-view-own'
                ],
            ];
            $newRole = $this->permissionsRepo->saveNewRole($roleData);
            $newUser->attachRole($newRole);
        }
            $this->login($user, $remember);
            return true;

        return false;
    }
}
