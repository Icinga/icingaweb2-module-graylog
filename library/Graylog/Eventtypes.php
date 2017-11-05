<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Graylog;

use Icinga\Repository\IniRepository;

class Eventtypes extends IniRepository
{
    protected $configs = [
        'eventtypes' => [
            'name'      => 'eventtypes',
            'keyColumn' => 'name',
            'module'    => 'graylog'
        ]
    ];

    protected $queryColumns = [
        'eventtypes' => [
            'name',
            'instance',
            'index', //TODO - stream?
            'filter',
            'fields'
        ]
    ];
}