<?php

namespace BookStack\Access\Guards;

use BookStack\Access\TruesightDevopsService;
use BookStack\Permissions\PermissionsRepo;
use BookStack\Users\UserRepo;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;

class TruesightDevopsSessionGuard extends SessionGuard
{
    use GuardHelpers;

    protected TruesightDevopsService $truesightDevopsService;
    protected PermissionsRepo $permissionsRepo;
    protected UserRepo $userRepo;

    private $defaultPermissions = [
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
    ];

    /**
     * TruesightDevopsSessionGuard constructor.
     */
    public function __construct(
        $name,
        UserProvider $provider,
        Session $session,
        TruesightDevopsService $truesightDevopsService,
        PermissionsRepo $permissionsRepo,
        UserRepo $userRepo,
    ) {
        $this->truesightDevopsService = $truesightDevopsService;
        $this->userRepo = $userRepo;
        $this->permissionsRepo = $permissionsRepo;
        parent::__construct($name, $provider, $session);
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
        $this->fireAttemptEvent($credentials, $remember);

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        $username = $credentials['username'];
        $password = $credentials['password'];

        $devopsUser = $this->truesightDevopsService->getInfo($username, $password);

        // If the authentication attempt fails we will fire an event so that the user
        // may be notified of any suspicious attempts to access their account from
        // an unrecognized user. A developer may listen to this event as needed.
        if ($devopsUser == null) {
            $this->fireFailedEvent($user, $credentials);
            return false;
        }

        $credentials['name'] = $devopsUser->name;
        $user = $this->userRepo->getByUsername($username);

        // Bootstrap new user
        if ($user == null) {
            // Create the user
            $newUser = $this->userRepo->createWithoutActivity($credentials, true);

            // Create & assign user-specific role
            $roleData = [
                'display_name' => $username,
                'description' => 'Role for ' . $username,
                'mfa_enforced' => false,
                'permissions' => $this->defaultPermissions,
            ];
            $newRole = $this->permissionsRepo->saveNewRole($roleData);
            $newUser->attachRole($newRole);
        }

        $this->login($user, $remember);
        return true;
    }
}
