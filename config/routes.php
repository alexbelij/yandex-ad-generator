<?php

return [
    '<action:(login|logout)>' => 'security/<action>',
    '/f/<hash:\w+>' => '/feed/feed/redirect',
    '/q/<urlPart:.+>' => '/feed/feed/quick-redirect',
];