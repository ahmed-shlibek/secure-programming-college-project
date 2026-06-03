<?php

class PageController
{
    public function dashboard(): void
    {
        $user      = User::findById((int) $_SESSION['user_id']);
        $pageTitle = 'Dashboard';
        $activePage = 'dashboard';
        require __DIR__ . '/../../views/pages/dashboard.php';
    }

    public function about(): void
    {
        $pageTitle  = 'About';
        $activePage = 'about';
        $error      = flash('error');
        $success    = flash('success');
        require __DIR__ . '/../../views/pages/about.php';
    }
}
