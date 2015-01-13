<?php

namespace Foolz\Foolfuuka\Plugins\NASMediaPurge\Model;

use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\Model;
use Foolz\Foolframe\Model\Preferences;
use Foolz\Foolfuuka\Model\Media;

use Predis\Client;
use Predis\CommunicationException;

class NASMediaPurge extends Model
{
    /**
     * @var Preferences
     */
    protected $preferences;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->preferences = $context->getService('preferences');
    }

    public function beforeDeleteMedia($result)
    {
        /** @var Media $post */
        $post = $result->getObject();
        $path = [];

        try {
            $path['image'] = $post->getDir(false, true, true);
        } catch (\Foolz\Foolfuuka\Model\MediaException $e) {

        }

        try {
            $post->op = 0;
            $path['thumb-0'] = $post->getDir(true, true, true);
        } catch (\Foolz\Foolfuuka\Model\MediaException $e) {

        }

        try {
            $post->op = 1;
            $path['thumb-1'] = $post->getDir(true, true, true);
        } catch (\Foolz\Foolfuuka\Model\MediaException $e) {

        }

        $this->purge($path);
    }

    public function getServer()
    {
        return $this->preferences->get('foolfuuka.plugin.nas_media_purge.server');
    }

    public function purge($path)
    {
        if (($server = $this->getServer())) {
            $connection = new Client($server);
            try {
                $connection->connect();
                foreach ($path as $k => $file) {
                    if (null !== $file) {
                        $connection->publish('foolfuuka:plugin:nas-media-purge', $path[$k]);
                    }
                }
            } catch (CommunicationException $e) {

            }
        }

        return null;
    }
}
