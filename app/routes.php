<?php

return [
    // Authentication
    ['GET',  '/login',     'AuthController',    'showLogin'],
    ['POST', '/login',     'AuthController',    'login'],
    ['GET',  '/register',  'AuthController',    'showRegister'],
    ['POST', '/register',  'AuthController',    'register'],
    ['POST', '/logout',    'AuthController',    'logout'],

    // Pages
    ['GET',  '/about',     'PageController',    'about'],

    // Contact form (authenticated)
    ['POST', '/contact',   'ContactController', 'submit'],
];
