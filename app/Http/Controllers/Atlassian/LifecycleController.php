<?php

namespace App\Http\Controllers\Atlassian;

use App\Http\Controllers\Controller;
use App\JIRA\Tenant;
use Request;

/**
 * Class LifecycleController
 * @package App\Http\Controllers
 */
class LifecycleController extends Controller
{
    public function installed()
    {
        $content = Request::getContent();

        $content = json_decode($content, true);
        if (!$content) {
            return 'ERROR: No content provided.';
        }

        $tenant = Tenant::fromClientKey($content['clientKey']);
        if (!$tenant) {
            $tenant = new Tenant();
        }

        $tenant->key = $content['key'];
        $tenant->clientKey = $content['clientKey'];
        $tenant->publicKey = $content['publicKey'];
        $tenant->sharedSecret = $content['sharedSecret'];
        $tenant->serverVersion = $content['serverVersion'];
        $tenant->pluginsVersion = $content['pluginsVersion'];
        $tenant->baseUrl = $content['baseUrl'];
        $tenant->productType = $content['productType'];
        $tenant->description = $content['description'];
        $tenant->eventType = $content['eventType'];

        $tenant->save();

        return 'OK';
    }
}