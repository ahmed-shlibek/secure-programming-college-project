<?php

namespace App\Controllers;

class PageController
{
    public function about(): void
    {
        $pageTitle  = 'About';
        $activePage = 'about';
        $error      = flash('error');
        $success    = flash('success');
        require __DIR__ . '/../../views/pages/about.php';
    }
}
