<?php

namespace App\Http\Controllers;

use App\JIRA\Tenant;
use Auth;
use Request;

/**
 * Class AdminController
 * @package App\Http\Controllers
 */
class AdminController extends Controller
{
    public function home()
    {
        $tenant = Tenant::getAuthenticatedTenant();
        $user = Auth::getUser();

        return view('admin.app', [ 'tenant' => $tenant, 'user' => $user ]);
    }

    public function projects()
    {
        return 'fubar';
    }
}