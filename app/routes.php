<?php

return [
    // Authentication
    ['GET',  '/login',     'AuthController',    'showLogin'],
    ['POST', '/login',     'AuthController',    'login'],
    ['GET',  '/register',  'AuthController',    'showRegister'],
    ['POST', '/register',  'AuthController',    'register'],
    ['POST', '/logout',    'AuthController',    'logout'],

    // Pages
    ['GET',  '/dashboard', 'PageController',    'dashboard'],
    ['GET',  '/about',     'PageController',    'about'],

    // Contact form (public)
    ['POST', '/contact',   'ContactController', 'submit'],
];
