<?php

namespace Foolz\FoolFrame\Controller\Admin\Plugins;

use Foolz\FoolFrame\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NASMediaPurge extends \Foolz\FoolFrame\Controller\Admin
{
    public function before()
    {
        parent::before();

        $this->param_manager->setParam('controller_title', 'NAS Media Purge');
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.admin');
    }

    function structure()
    {
        return [
            'open' => [
                'type' => 'open',
            ],
            'foolfuuka.plugin.nas_media_purge.server' => [
                'type' => 'input',
                'preferences' => true,
                'label' => _i('Queue Server'),
                'help' => _i(''),
                'class' => 'span8',
                'validation' => [new Trim()]
            ],
            'separator-2' => [
                'type' => 'separator-short'
            ],
            'submit' => [
                'type' => 'submit',
                'class' => 'btn-primary',
                'value' => _i('Submit')
            ],
            'close' => [
                'type' => 'close'
            ],
        ];
    }

    function action_manage()
    {
        $this->param_manager->setParam('method_title', 'Manage');

        $data['form'] = $this->structure();

        $this->preferences->submit_auto($this->getRequest(), $data['form'], $this->getPost());
        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
