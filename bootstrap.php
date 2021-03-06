<?php

use Foolz\FoolFrame\Model\Autoloader;
use Foolz\FoolFrame\Model\Context;
use Foolz\Plugin\Event;

class HHVM_NASMediaPurge
{
    public function run()
    {
        Event::forge('Foolz\Plugin\Plugin::execute#foolz/foolfuuka-plugin-nas-media-purge')
            ->setCall(function ($result) {
                /* @var Context $context */
                $context = $result->getParam('context');
                /** @var Autoloader $autoloader */
                $autoloader = $context->getService('autoloader');

                $autoloader->addClassMap([
                    'Foolz\FoolFrame\Controller\Admin\Plugins\NASMediaPurge' => __DIR__ . '/classes/controller/admin.php',
                    'Foolz\FoolFuuka\Plugins\NASMediaPurge\Model\NASMediaPurge' => __DIR__ . '/classes/model/purge.php'
                ]);

                $context->getContainer()
                    ->register('foolfuuka-plugin.nas_media_purge', 'Foolz\FoolFuuka\Plugins\NASMediaPurge\Model\NASMediaPurge')
                    ->addArgument($context);

                Event::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.afterAuth')
                    ->setCall(function ($result) use ($context) {
                        // don't add the admin panels if the user is not an admin
                        if ($context->getService('auth')->hasAccess('maccess.admin')) {
                            $context->getRouteCollection()->add(
                                'foolfuuka.plugin.nas_media_purge.admin', new \Symfony\Component\Routing\Route(
                                    '/admin/plugins/nas_media_purge/{_suffix}',
                                    [
                                        '_suffix' => 'manage',
                                        '_controller' => 'Foolz\FoolFrame\Controller\Admin\Plugins\NASMediaPurge::manage'
                                    ],
                                    [
                                        '_suffix' => '.*'
                                    ]
                                )
                            );

                            Event::forge('Foolz\FoolFrame\Controller\Admin::before#var.sidebar')
                                ->setCall(function ($result) {
                                    $sidebar = $result->getParam('sidebar');
                                    $sidebar[]['plugins'] = [
                                        'content' => ['nas_media_purge/manage' => ['level' => 'admin', 'name' => 'NAS Media Purge', 'icon' => 'icon-leaf']]
                                    ];
                                    $result->setParam('sidebar', $sidebar);
                                });
                        }
                    });

                Event::forge('Foolz\FoolFuuka\Model\Media::delete#call.beforeMethod')
                    ->setCall(function ($result) use ($context) {
                        $context->getService('foolfuuka-plugin.nas_media_purge')->beforeDeleteMedia($result);
                    });
            });
    }
}

(new HHVM_NASMediaPurge())->run();
