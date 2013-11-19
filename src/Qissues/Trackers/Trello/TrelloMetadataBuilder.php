<?php

namespace Qissues\Trackers\Trello;

use Guzzle\Http\Client;
use Qissues\System\Storage\LocalStorage;

class TrelloMetadataBuilder
{
    protected $boardName;
    protected $storageKey;
    protected $query;
    protected $client;
    protected $storage;

    public function __construct($boardName, $key, $token, LocalStorage $storage, Client $client = null)
    {
        $this->boardName = $boardName;
        $this->storageKey = 'trello' . md5($boardName);
        $this->query = array('key' => $key, 'token' => $token);
        $this->storage = $storage;
        $this->client  = $client ?: new Client('https://trello.com/', array('ssl.certificate_authority' => 'system'));
    }

    public function build()
    {
        if ($this->storage->exists($this->storageKey)) {
            return new Metadata($this->storage->get($this->storageKey));
        }
    }

    public function update()
    {
        $request = $this->client->get('/1/members/my/boards');
        $request->getQuery()->merge($this->query);
        $request->getQuery()->merge(array(
            'lists' => 'open'
        ));

        $response = $request->send()->json();
        $found = false;

        foreach ($response as $board) {
            if ($board['name'] != $this->boardName) {
                continue;
            }

            $found = true;
            $lists = array();
            foreach ($board['lists'] as $list) {
                $lists[$list['name']] = array(
                    'id' => $list['id'],
                    'name' => $list['name'],
                    'pos' => $list['pos']
                );
            }

            usort($lists, function($a, $b) {
                if ($a['pos'] == $b['pos']) return 0;
                return $a['pos'] < $b['pos'] ? -1 : 1;
            });

            $this->storage->set($this->storageKey, array(
                'id' => $board['id'],
                'name' => $board['name'],
                'labels' => $board['labelNames'],
                'lists' => $lists
            ));
        }

        if (!$found) {
            throw new \Exception('Could not find Trello board; do you have the right name?');
        }
    }
}
