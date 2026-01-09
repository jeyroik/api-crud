<?php

use jeyroik\components\repositories\plugins\RepoPluginDates;
use jeyroik\components\repositories\plugins\RepoPluginUuid;

return [
    RepoPluginUuid::class => [
        RepoPluginUuid::OPTION__REWRITE => RepoPluginUuid::REWRITE_OFF
    ],
    RepoPluginDates::class => []
];
