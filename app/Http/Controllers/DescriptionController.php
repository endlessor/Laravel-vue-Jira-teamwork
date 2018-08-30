<?php

namespace App\Http\Controllers;


class DescriptionController extends Controller
{
    public function getDescription()
    {
        $out = [
            'name' => 'Teamwork Sync',
            'description' => 'Syncronize issues from Atlassian JIRA to Teamwork (one way), including converting calculated fields.',
            'key' => "eu.catlab.jira-connect-plugin.teamworksync",
            'baseUrl' => url('', [], true),
            'vendor' => [
                'name' => 'CatLab Interactive',
                'url' => 'https://www.catlab.eu'
            ],
            'authentication' => [
                'type' => 'jwt'
            ],
            'scopes' => [
                'read'
            ],
            'apiVersion' => 1,
            'lifecycle' => [
                'installed' => '/lifecycle/installed'
            ],
            'modules' => [
                'adminPages' => [
                    [
                        'url' => '/admin',
                        'key' => 'admin',
                        'name' => [
                            'value' => "Teamwork Sync"
                        ]
                    ]
                ],

                'webhooks' => [
                    [
                        'event' => 'jira:issue_created',
                        'url' => '/jira/issues/created',
                        'excludedBody' => false
                    ],
                    [
                        'event' => 'jira:issue_updated',
                        'url' => '/jira/issues/updated',
                        'excludedBody' => false
                    ],
                    [
                        'event' => 'jira:issue_deleted',
                        'url' => '/jira/issues/deleted',
                        'excludedBody' => false
                    ]
                ]
            ]
        ];

        return \Response::json($out);
    }
}